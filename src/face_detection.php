<?php
// Get the image data from the AJAX request
$imageData = $_POST['image'];

// Save the image data to a file
$imageFile = 'frame.jpg';
file_put_contents($imageFile, base64_decode(str_replace('data:image/jpeg;base64,', '', $imageData)));

// Run the face detection Python script using shell_exec
$command = 'python face_detection.py ' . escapeshellarg($imageFile);
$output = shell_exec($command);

//echo $output
// Process the output as needed and return the results
// For example, you can check the number of detected faces and return a response accordingly
$num_faces = intval(trim($output));

//echo $num_faces
if ($output) {
    echo $output;
}
elseif($num_faces == 0 ){
    echo 'no_faces';
}
elseif($num_faces == 1 ){
    echo 'single_face';
}else {
    echo $num_faces;
}

// Cleanup - delete the temporary image file
unlink($imageFile);
?>
