<!DOCTYPE html>
<html>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.js"></script>
<body>
<canvas id="myChart" style="width:100%;max-width:600px"></canvas>

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
          type: "line",
          fill: false,
          lineTension: 0,
          pointRadius: 0,
          backgroundColor:  "rgba(0,0,255,1.0)",
          borderColor:      "rgba(0,0,255,0.1)",
          data: dv.data[i].data,
          label: dv.data[i].label
        };
        sv = sv.map((n,j) => n + dv.data[i].data[j]);
        fv.push(ev);
        // console.log(ev);
    }

    let ev = {
      type: "line",
      fill: false,
      lineTension: 0,
      pointRadius: 0,
    //   backgroundColor: "rgba(0,0,255,1.0)",
    //   borderColor: "rgba(0,0,255,0.1)",
      data: sv,
      label: "Total"
    };
    fv.push(ev);
    console.log(fv);
    
//     sm = sv.reduce((a, b) => a + b, 0) / sv.length;
//     valtime = valleys.map((r,i)=>r*xscale);
// 
//     ev = {
//       type: "Scatter",
//       fill: false,
//       lineTension: 0,
//       pointRadius: 5,
//     //   backgroundColor: "rgba(0,0,255,1.0)",
//     //   borderColor: "rgba(0,0,255,0.1)",
//       data: valtime,
//       label: "Stands",
//     };
//     fv.push(ev);

    new Chart("myChart", {
      type: "line",
      data: {
        labels: xv,
        datasets: fv
      },
      options: {
        legend: {display: true},
        title: {
            display: true,
            text: title
        },
        scales: {
            yAxes: {
                display: true,
                labelString: ylabel
            },
            xAxes: {
                display: true,
                labelString: xlabel
            }
        }
      }
    });
}
</script>

</body>
</html>

