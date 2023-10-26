const http = require('http');
const fs = require('fs');

const hostname = '127.0.0.1';
const port = 3000;

var data = [];
var metadata = {};
const datafolder = './uploads/';
const datafile = datafolder + 'sample.json';

function getvalleys1(data, threshold, width) {
    let valleys = [];
    let numval = width;

    for (let i in data)
        if (data[i] < threshold) {
            if (numval > 0) {
                numval -= 1;
                if (numval <= 0)
                    valleys.push(i);
            }
        } else
            numval = width;

    return valleys;
}

function get_data(payload) {
    switch (payload['type']) {
        case 'command':
            if (payload['command'] == 'save') {
                let content = { "metadata": metadata, "data": data };

                // if (isset($_SESSION['metadata']['user']))
                //     $user = $_SESSION['metadata']['user'];
                // else
                //     $user = 0;

                // $i = 0;
                // while (file_exists($datafile = $datafolder.sprintf("sample%03d-%03d.json", $user, $i)))
                //     $i += 1;



                fs.writeFile(datafile, JSON.stringify(content), (err) => {
                    if (err)
                        console.log(err);
                });
                return "saved";
            } else
                return "unknown command";
            break;

        case 'metadata':
            metadata = payload['metadata'];
            data = [];
            //         console.log(JSON.stringify(metadata))
            return "metadata received";
            break;

        case 'data':
            data.push(payload['data']);
            //         console.log(JSON.stringify(data))
            return "data added";
            break;

        default:
            return "unknown type";
            break;
    }
}

const server = http.createServer((req, res) => {
    console.log(req.url);

    switch (req.url) {
        case '/':
            fs.readFile('./templates/index.html', function (err, html) {
                res.writeHead(200, { 'Content-Type': 'text/html' });
                res.write(html);
                return res.end();
            });
            break;


        case '/data':
            fs.readdir('/var/', function(err, files){
                files.forEach(function(file){
                    res.write("<a href=/data?" + file + ">" + file + "</a><br>");
                });
            });
            fs.readFile(datafile, function (err, html) {
                res.writeHead(200, { 'Content-Type': 'application/json' });
                res.write(html);
                return res.end();
            });
            break;

        case '/upload':
            let body = '';

            req.on('data', buffer => {
                body += buffer.toString(); // convert Buffer to string
            });

            req.on('end', () => {
                let content = JSON.parse(body);
                res.writeHead(200, { 'Content-Type': 'application/json' });
                let ret = get_data(content);
                res.write(JSON.stringify({ ret: true }));
                return res.end();
            });

            break;



        case '/valleys':
            fs.readFile(datafile, function (err, html) {
                let dv = JSON.parse(html);
                let len = dv.data[0].data.length;
                let sv = Array(len).fill(0);
                for (let i in dv.data) {
                    sv = sv.map((n, j) => n + dv.data[i].data[j]);
                }
                let sm = sv.reduce((a, b) => a + b, 0) / sv.length;
                let valleys = getvalleys1(sv, sm, 10);
                res.writeHead(200, { 'Content-Type': 'application/json' });
                res.write(JSON.stringify(valleys));
                return res.end();
            });
            break;

        case '/linect':
            fs.readFile('linect.html', function (err, html) {
                res.writeHead(200, { 'Content-Type': 'text/html' });
                res.write(html);
                return res.end();
            });
            break;

        case '/linepl':
            fs.readFile('linepl.html', function (err, html) {
                res.writeHead(200, { 'Content-Type': 'text/html' });
                res.write(html);
                return res.end();
            });
            break;

        case '/linepl1':
            fs.readFile('linepl1.html', function (err, html) {
                res.writeHead(200, { 'Content-Type': 'text/html' });
                res.write(html);
                return res.end();
            });
            break;

        case '/linegl':
            fs.readFile('linegl.html', function (err, html) {
                res.writeHead(200, { 'Content-Type': 'text/html' });
                res.write(html);
                return res.end();
            });
            break;

        case '/linemt':
            break;

    }
});

server.listen(process.env.PORT || 3000, () => {
    console.log(`Server running at http://${hostname}:${port}/`);
});
