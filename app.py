
# pip install flask-sock
from flask import Flask, request, jsonify, Response, render_template
import io
import os
from matplotlib.backends.backend_agg import FigureCanvasAgg as FigureCanvas
from matplotlib.figure import Figure
import matplotlib.pyplot as plt
import json
from pathlib import Path

plt.rcParams["figure.figsize"] = [7.50, 3.50]
plt.rcParams["figure.autolayout"] = True

data = []
metadata = {}

xscale = 12
title = 'Weight vs Time'
xlabel = "Time (ms)"
ylabel = "Weight (0.1lb)"
THIS_FOLDER = Path(__file__).parent.resolve()
datafolder = THIS_FOLDER / "uploads/"


def getvalleys1(data, threshold, width):
  valleys = []
  numval = width

  for i in range(len(data)):
    if (data[i] < threshold):
      if (numval > 0):
        numval -= 1
        if (numval <=0):
          valleys.append(i)
    else:
      numval = width
  return valleys

def getderiv(data):
    deriv = [0]

    for i in range(len(data)-1):
        deriv.append(data[i+1] - data[i])

    return deriv

#{"metadata":{...}, "data":[]}

def get_data(payload):
    global xscale, metadata, data

    if payload['type'] == 'command':
        if payload['command'] == 'save':
            content = {"metadata":metadata, "data":data}
            
            if 'user' in metadata:
                user = metadata['user']
            else:
                user = 0
            
            i = 0
            while os.path.exists(datafolder / ("sample%03d-%03d.json" % (user, i))):
                i += 1
            datafile = datafolder / ("sample%03d-%03d.json" % (user, i))

            with open(datafile, "w") as outfile:
                json.dump(content, outfile)
            return "saved"
        return "unknown command"
    
    elif payload['type'] == 'metadata':
        metadata = payload['metadata']
        data = []
        return "metadata received"
    
    elif payload['type'] == 'data':
        data.append(payload['data'])
        return "data added"
    
    else:
        return "unknown type"

app = Flask(__name__)

# ### REMOVE THIS FOR pythonanywhere
# from flask_sock import Sock
# sock = Sock(app)
# @sock.route('/echo')
# def echo(sock):
#     while True:
#         data = sock.receive()
#         sock.send(data)

@app.route('/')
def hello_world():
#     return 'Data Graphing'
    return render_template('index.html')    

@app.route('/upload', methods=['POST'])
def upload_data():
    content = request.get_json()
    ret = get_data(content)
    return jsonify({ret: True})

@app.route('/data')
def data_dir():
    # Show directory contents
    files = os.listdir(datafolder)
    files = sorted(files)
    return render_template('files.html', files=files, title='Open Data File')

@app.route('/data/<filename>')
def data_show(filename):
    with open(datafolder/f'{filename}', "r") as infile:
        content = json.load(infile)
    return jsonify(content)

@app.route('/deriv')
def deriv_dir():
    # Show directory contents
    files = os.listdir(datafolder)
    files = sorted(files)
    return render_template('files.html', files=files, title='Plot Rate of Change')

@app.route('/deriv/<filename>')
def deriv(filename):
    with open(datafolder/f'{filename}', "r") as infile:
        content = json.load(infile)

    xscale = content['metadata']['T_sample']
    data = content['data']

    dtasum = [0] * len(data[0]['data'])
    for pkg in data:
        dtasum = [dtasum[i] + pkg['data'][i] for i in range(len(dtasum))]

    deriv = getderiv(dtasum)

    return jsonify(deriv)


@app.route('/valleys')
def valleys_dir():
    # Show directory contents
    files = os.listdir(datafolder)
    files = sorted(files)
    return render_template('files.html', files=files, title='Get Valleys of Data File')

@app.route('/valleys/<filename>')
def valleys(filename):
    with open(datafolder/f'{filename}', "r") as infile:
        content = json.load(infile)

    xscale = content['metadata']['T_sample']
    data = content['data']

    dtasum = [0] * len(data[0]['data'])
    for pkg in data:
        dtasum = [dtasum[i] + pkg['data'][i] for i in range(len(dtasum))]

    valleys = getvalleys1(dtasum, sum(dtasum)/len(dtasum), 1)

    return jsonify(valleys)

@app.route('/mqttcon')
def con():
    return render_template('mqttcon.html')

@app.route('/mqttixf')
def cmd():
    return render_template('mqttixf.html')


@app.route('/plotmt/<filename>')
def plotmt(filename):
    fig = Figure()
    axis = fig.add_subplot(1, 1, 1)

    with open(datafolder/f'{filename}', "r") as infile:
        content = json.load(infile)

    xscale = content['metadata']['T_sample']
    data = content['data']

    dtasum = [0] * len(data[0]['data'])
    for pkg in data:
        axis.plot([i*xscale for i in range(len(pkg['data']))], pkg['data'], label=pkg['label'])
        dtasum = [dtasum[i] + pkg['data'][i] for i in range(len(dtasum))]
    axis.plot([i*xscale for i in range(len(pkg['data']))], dtasum, label='total')

    valleys = getvalleys1(dtasum, sum(dtasum)/len(dtasum), 1)
    valtime = [i*xscale for i in valleys]
    axis.scatter(valtime, [sum(dtasum)/len(dtasum)] * len(valleys))

    axis.set_xlabel(xlabel)
    axis.set_ylabel(ylabel)
    axis.set_title(title)

    output = io.BytesIO()
    FigureCanvas(fig).print_png(output)
    return Response(output.getvalue(), mimetype='image/png') 

@app.route('/plotderiv/<filename>')
def plotderiv(filename):
    return render_template('plotderiv.html', filename=filename)

@app.route('/plotpl/<filename>')
def plotpl(filename):
    return render_template('plotpl.html', filename=filename)

@app.route('/plotpl1/<filename>')
def plotpl1(filename):
    return render_template('plotpl1.html', filename=filename)

@app.route('/plotct/<filename>')
def plotct(filename):
    return render_template('plotct.html', filename=filename)

@app.route('/plotgl/<filename>')
def plotgl(filename):
    return render_template('plotgl.html', filename=filename)

@app.route('/plotderiv')
@app.route('/plotmt')
@app.route('/plotgl')
@app.route('/plotct')
@app.route('/plotpl')
@app.route('/plotpl1')
def plot_dir():
    # Show directory contents
    files = os.listdir(datafolder)
    files = sorted(files)
    return render_template('files.html', files=files, title='Plot Data File')

if __name__ == "__main__":        # on running python app.py
    app.run(debug=True)                     # run the flask app
