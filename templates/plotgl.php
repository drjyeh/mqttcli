<!DOCTYPE html>
<html>
<script src="https://www.gstatic.com/charts/loader.js"></script>
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
    
google.charts.load('current',{packages:['corechart']})
    .then(() =>
	fetch(urldata)
 	   .then((response) => response.json())
 	   .then((json) => {dv = json; 
    		fetch(urlvalleys)
    			.then((response) => response.json())
    			.then((json) => {valleys = json; doit(); });})
    
    );

function doit() {
    xscale = dv.metadata.T_sample;
    len = dv.data[0].data.length;
    var xv = Array.from(Array(len), (_,i)=> i*xscale);
    var sv = Array(len).fill(0);

    ev = ["Time"];
    ev.push("Stands");
    for (i in dv.data) {
        ev.push(dv.data[i].label);
    }
    ev.push("Total");

    fv = [ev];
    ;
    sm = 1000
    valtime = valleys.map((r,i)=>r*xscale);

    for (i in xv) {
        let ev = [xv[i]];
        if (valtime.includes(xv[i]))
        	ev.push(sm);
        else
        	ev.push(0);        
        let sv = 0;
        for (j in dv.data) {
            ev.push(dv.data[j].data[i]);
            sv += dv.data[j].data[i];
        }
        ev.push(sv);
        fv.push(ev);
    }

    var data = google.visualization.arrayToDataTable(fv);

    console.log(fv);

    const options = {
      hAxis: {title: xlabel},
      vAxis: {title: ylabel},
      title: title,
      legend: { position: 'bottom' }
    };

    var chart = new google.visualization.LineChart(document.getElementById('myChart'));

    chart.draw(data, options);
}
</script>

</body>
</html>

