

<?php
session_start();
error_reporting(1);
include("database.php");
extract($_POST);
extract($_GET);
extract($_SESSION);

if (isset($subid) && isset($testid)) {
    $_SESSION['sid'] = $subid;
    $_SESSION['tid'] = $testid;
    header("location:quiz.php");
}

if (!isset($_SESSION['sid']) || !isset($_SESSION['tid'])) {
    header("location: index.php");
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
    <title>Online Quiz</title>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
    <link href="quiz.css" rel="stylesheet" type="text/css">
</head>

<body onload = "startFaceProctoring()">
<?php include("header.php"); ?>

<!-- Add the video, canvas, and button HTML code here -->
<video id="video" width="300" height="200" autoplay></video>
<canvas id="canvas" width="300" height="200"></canvas>
<!-- <button onclick="startFaceProctoring()">Start Face Proctoring</button> -->
<!-- <script>startFaceProctoring()</script> -->

<?php
$query = "SELECT * FROM mst_question";
$rs = mysqli_query($cn, "SELECT * FROM mst_question WHERE test_id=$tid") or die(mysqli_error($cn));

if (!isset($_SESSION['qn'])) {
    $_SESSION['qn'] = 0;
    mysqli_query($cn, "DELETE FROM mst_useranswer WHERE sess_id='" . session_id() . "'") or die(mysqli_error($cn));
    $_SESSION['trueans'] = 0;
} else {
    if ($submit == 'Next Question' && isset($ans)) {
        mysqli_data_seek($rs, $_SESSION['qn']);
        $row = mysqli_fetch_row($rs);
        mysqli_query($cn, "INSERT INTO mst_useranswer (sess_id, test_id, que_des, ans1, ans2, ans3, ans4, true_ans, your_ans) VALUES ('" . session_id() . "', $tid, '$row[2]', '$row[3]', '$row[4]', '$row[5]', '$row[6]', '$row[7]', '$ans')") or die(mysqli_error($cn));
        if ($ans == $row[7]) {
            $_SESSION['trueans'] = $_SESSION['trueans'] + 1;
        }
        $_SESSION['qn'] = $_SESSION['qn'] + 1;
    } else if ($submit == 'Get Result' && isset($ans)) {
        mysqli_data_seek($rs, $_SESSION['qn']);
        $row = mysqli_fetch_row($rs);
        mysqli_query($cn, "INSERT INTO mst_useranswer (sess_id, test_id, que_des, ans1, ans2, ans3, ans4, true_ans, your_ans) VALUES ('" . session_id() . "', $tid, '$row[2]', '$row[3]', '$row[4]', SUBSTRING('$row[5]', 1, 255), '$row[6]', '$row[7]', '$ans')") or die(mysqli_error($cn));
        if ($ans == $row[7]) {
            $_SESSION['trueans'] = $_SESSION['trueans'] + 1;
        }
        echo "<h1 class=head1> Result</h1>";
        $_SESSION['qn'] = $_SESSION['qn'] + 1;
        echo "<table align=center><tr class=tot><td>Total Question<td> $_SESSION[qn]";
        echo "<tr class=tans><td>True Answer<td>" . $_SESSION['trueans'];
        $w = $_SESSION['qn'] - $_SESSION['trueans'];
        echo "<tr class=fans><td>Wrong Answer<td> " . $w;
        echo "</table>";
        mysqli_query($cn, "INSERT INTO mst_result (login, test_id, test_date, score) VALUES ('$login', $tid, '" . date("Y-m-d") . "', $_SESSION[trueans])") or die(mysqli_error($cn));
        echo "<h1 align=center><a href=review.php> Review Question</a> </h1>";
        unset($_SESSION['qn']);
        unset($_SESSION['sid']);
        unset($_SESSION['tid']);
        unset($_SESSION['trueans']);
        exit;
    }
}

$rs = mysqli_query($cn, "SELECT * FROM mst_question WHERE test_id=$tid") or die(mysqli_error($cn));

if ($_SESSION['qn'] > mysqli_num_rows($rs) - 1) {
    unset($_SESSION['qn']);
    echo "<h1 class=head1>Some Error Occurred</h1>";
    session_destroy();
    echo "Please <a href=index.php>Start Again</a>";
    exit;
}

mysqli_data_seek($rs, $_SESSION['qn']);
$row = mysqli_fetch_row($rs);

echo "<form name=myfm method=post action=quiz.php>";
echo "<table width=100%> <tr> <td width=30>&nbsp;<td> <table border=0>";
$n = $_SESSION['qn'] + 1;
echo "<tr><td><span class=style2>Que " . $n . ": $row[2]</style>";
echo "<tr><td class=style8><input type=radio name=ans value=1>$row[3]";
echo "<tr><td class=style8><input type=radio name=ans value=2>$row[4]";
echo "<tr><td class=style8><input type=radio name=ans value=3>$row[5]";
echo "<tr><td class=style8><input type=radio name=ans value=4>$row[6]";

if ($_SESSION['qn'] < mysqli_num_rows($rs) - 1) {
    echo "<tr><td><input type=submit name=submit value='Next Question'></form>";
} else {
    echo "<tr><td><input type=submit name=submit value='Get Result'></form>";
}
echo "</table></table>";
?>
<script>
// Function to send image frames to the server for face detection
function sendImageFrame() {
    // Capture a frame from the video
    context.drawImage(video, 0, 0, canvas.width, canvas.height);
    const imageData = canvas.toDataURL('image/jpeg');

    // Send the image data to the server using AJAX
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'face_detection.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            // Process the response from the server if needed
            const response = xhr.responseText;
            //console.log(response);
            if (response > 1 ) {
                 console.log("exit");
                 window.location.href = 'signout.php'; // Replace 'exit.php' with the URL or file where you handle the system exit

                //console.log('Multiple faces detected. Exiting...');
                // Add code here to handle the system exit behavior
            }else if(response === 'no_faces'){
                 console.log('no faces detected');    
             }else if(response == 1){
                console.log('no multiple faces detected');
                // console.log(response);
            }else{
                console.log('no response');
            }
        }
    };
    xhr.send('image=' + encodeURIComponent(imageData));

    // Call this function again to continuously send frames
   // requestAnimationFrame(sendImageFrame);
}

// Function to start the face proctoring process
function startFaceProctoring() {
    
    
    // Start sending image frames to the server
    sendImageFrame();
    setTimeout(startFaceProctoring,200);
    // Add any additional functionality or actions you want to perform during face proctoring
    // For example, you can disable certain buttons or show notifications to the user
}

// Get the video and canvas elements
const video = document.getElementById('video');
const canvas = document.getElementById('canvas');
const context = canvas.getContext('2d');

// Request access to the camera
navigator.mediaDevices.getUserMedia({ video: true })
    .then(function(stream) {
        video.srcObject = stream;
    })
    .catch(function(error) {
        console.error('Error accessing the camera:', error);
    });
</script>
</body>
</html>
