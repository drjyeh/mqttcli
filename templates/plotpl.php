<!DOCTYPE html>
<html>
<script src="https://cdn.plot.ly/plotly-latest.min.js"></script>
<body>
<div id="myChart" style="width:100%;max-width:600px"></div>

<script>
urldata = window.location.protocol + "//" + window.location.host + "/data?" + filename;
urlvalleys = window.location.protocol + "//" + window.location.host + "/valleys?" + filename;

title = 'Weight vs Time';
xlabel = 'Time (ms)';
ylabel = 'Weight (0.1lb)';

var dv;
var valleys;
var ft;
var vt;

ft = fetch(urldata)
    .then((response) => response.json())
    .then((json) => {dv = json; 
    	vt = fetch(urlvalleys)
    		.then((response) => response.json())
    		.then((json) => {valleys = json; doit(); });});
    	
console.log(ft);

function doit() {
    xscale = dv.metadata.T_sample;
    len = dv.data[0].data.length;
    var xv = Array.from(Array(len), (_,i)=> i*xscale);
    var sv = Array(len).fill(0);

    fv = [];

    for (i in dv.data) {
        let ev = {
          x: xv,
          y: dv.data[i].data,
          name: dv.data[i].label,
          mode: "lines"
        };
        sv = sv.map((n,j) => n + dv.data[i].data[j]);
        fv.push(ev);
        // console.log(ev);
    }

    let ev = {
          x: xv,
          y: sv,
          name: "Total",
          mode: "lines"
    };
    fv.push(ev);
    console.log(fv);

    sm = sv.reduce((a, b) => a + b, 0) / sv.length;
    valtime = valleys.map((r,i)=>r*xscale);

    ev = {
          x: valtime,
          y: Array(valtime.length).fill(sm),
          name: "Stands",
          mode: "markers"
    };
    fv.push(ev);
    
    const layout = {
      xaxis: {title: xlabel},
      yaxis: {title: ylabel},
      title: title
    };

    Plotly.newPlot("myChart", fv, layout);
}
</script>

</body>
</html>

