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
            if (response === 'multiple_faces') {
                console.log('Multiple faces detected. Exiting...');
                // Add code here to handle the system exit behavior
                window.location.href = 'signout.php'; // Replace 'exit.php' with the URL or file where you handle the system exit
            }
        }
    };
    xhr.send('image=' + encodeURIComponent(imageData));

    // Call this function again to continuously send frames
    requestAnimationFrame(sendImageFrame);
}

// Function to start the face proctoring process
function startFaceProctoring() {
    // Start sending image frames to the server
    sendImageFrame();

    // Add any additional functionality or actions you want to perform during face proctoring
    // For example, you can disable certain buttons or show notifications to the user
}
