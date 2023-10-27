<?php
session_id('iot-chair-v2');
session_start();

if (!array_key_exists('metadata', $_SESSION))
    $_SESSION['metadata'] = array(); 

if (!array_key_exists('data', $_SESSION))
    $_SESSION['data'] = array(); 

// $datafile = './uploads/sample.json';
$datafolder = "./uploads/";

function getvalleys1($data, $threshold, $width) {
    $valleys = array();
    $numval = $width;

    for ($i = 0; $i < count($data); $i++)
        if ($data[$i] < $threshold) {
            if ($numval > 0) {
                $numval -= 1;
                if ($numval <=0)
                    array_push($valleys, $i);
            }
        } else
            $numval = $width;

  return $valleys;

}

function getderiv($data) {
    $len = count($data);
    $deriv = array_pad(array(), $len, 0);

    for ($i = 0; $i < $len-1; $i++) {
        $deriv[$i] = $data[$i+1] - $data[$i];
    }

    return $deriv;

}

function get_data($payload) {
	global $datafolder;
	
    switch ($payload['type']) {
    case 'command':
        if ($payload['command'] == 'save') {
            $content = array("metadata"=>$_SESSION['metadata'], "data"=>$_SESSION['data']);

            if (isset($_SESSION['metadata']['user']))
                $user = $_SESSION['metadata']['user'];
            else
                $user = 0;
            
            $i = 0;
            while (file_exists($datafile = $datafolder.sprintf("sample%03d-%03d.json",$user,$i)))
                $i += 1;

            $ret = file_put_contents($datafile, json_encode($content));
            // session_unset();
            $_SESSION['metadata'] = array();
            $_SESSION['data'] = array();
            return "saved[".$ret."]";
        } else
            return "unknown command";
    	break;
    	
    case 'metadata':
        $_SESSION['metadata'] = $payload['metadata'];
        $_SESSION['data'] = array();
        return "metadata received[".count($payload['metadata'])."]";
        break;
        
    case 'data':
        array_push($_SESSION['data'], $payload['data']);
        return "data added[".count($payload['data'])."]";
        break;
        
    default:
        return "unknown type";
        break;
	}
}

$req = parse_url($_SERVER['REQUEST_URI']);
switch ($req['path']) {

    case '/':
        header('Content-Type: text/html');
        readfile('./templates/index.html');
        break;

    case '/data':
        if (!isset($req['query'])) {
            // $directory = "./uploads/";
            $phpfiles = glob($datafolder . "*");
            header('Content-Type: text/html');
            foreach($phpfiles as $phpfile)
            {
                echo "<a href=/data?".$phpfile.">".basename($phpfile)."</a><br>";
            }
        } else {
            readfile($req['query']);
        }
        break;
        	
    case '/upload':
        $content = json_decode(file_get_contents('php://input'), true);
        $ret = get_data($content);
        header('Content-Type: application/json');
        echo json_encode(array($ret=>True));
    	
        break;

    case '/valleys':
        if (!isset($req['query'])) {
            // $directory = "./uploads/";
            $phpfiles = glob($datafolder . "*");
            header('Content-Type: text/html');
            foreach($phpfiles as $phpfile)
            {
                echo "<a href=/valleys?".$phpfile.">".basename($phpfile)."</a><br>";
            }
        } else {
            $html = file_get_contents($req['query']);
            $dv = json_decode($html, true);
            $len = count($dv['data'][0]['data']);

            $sv = array_pad(array(), $len, 0);
            foreach ($dv['data'] as $i) {
                for ($j = 0; $j < $len; $j++) {
                    $sv[$j] = 
                        $sv[$j] + $i['data'][$j];
                }
            }
    
            // $valleys = getvalleys1($sv, array_sum($sv) / $len, 10);
            $valleys = getvalleys1($sv, array_sum($sv) / $len, 1);
            header('Content-Type: application/json');
            echo json_encode($valleys);
        }
        break;

        case '/deriv':
            if (!isset($req['query'])) {
                // $directory = "./uploads/";
                $phpfiles = glob($datafolder . "*");
                header('Content-Type: text/html');
                foreach($phpfiles as $phpfile)
                {
                    echo "<a href=/deriv?".$phpfile.">".basename($phpfile)."</a><br>";
                }
            } else {
                $html = file_get_contents($req['query']);
                $dv = json_decode($html, true);
                $len = count($dv['data'][0]['data']);
    
                $sv = array_pad(array(), $len, 0);
                foreach ($dv['data'] as $i) {
                    for ($j = 0; $j < $len; $j++) {
                        $sv[$j] = 
                            $sv[$j] + $i['data'][$j];
                    }
                }
        
                $deriv = getderiv($sv);
                header('Content-Type: application/json');
                echo json_encode($deriv);
            }
            break;

    case '/mqttcon':
        header('Content-Type: text/html');
        readfile('./templates/mqttcon.html');
        break;
    
    case '/mqttixf':
        header('Content-Type: text/html');
        readfile('./templates/mqttixf.html');
        break;

    case '/linect':
        header('Content-Type: text/html');
        readfile('./html/linect.html');
        break;

    case '/linegl':
        header('Content-Type: text/html');
        readfile('./html/linegl.html');
        break;

    case '/plotderiv':
    case '/plotct':
    case '/plotgl':
    case '/plotpl':
        if (!isset($req['query'])) {
            // $directory = "./uploads/";
            $phpfiles = glob($datafolder . "*");
            header('Content-Type: text/html');
            foreach($phpfiles as $phpfile)
            {
                echo "<a href=".$req['path']."?".$phpfile.">".basename($phpfile)."</a><br>";
            }
        } else {
            header('Content-Type: text/html');
            echo "<script>var filename='".$req['query']."'; </script>";
            readfile("./templates/".$req['path'].".php");
        }
        break;

    case '/linepl1':
        header('Content-Type: text/html');
        readfile('./html/linepl1.html');
        break;
    default:
        echo "NOT FOUND";
}

?>
