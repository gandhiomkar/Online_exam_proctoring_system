import sys
import cv2

# Load the pre-trained Haar Cascade classifier for face detection
face_cascade = cv2.CascadeClassifier(cv2.data.haarcascades + 'haarcascade_frontalface_default.xml')

# Read the image file path from the command-line arguments
image_file = sys.argv[1]

# Load the image
frame = cv2.imread(image_file)

# Convert the frame to grayscale for face detection
gray = cv2.cvtColor(frame, cv2.COLOR_BGR2GRAY)

# Detect faces in the frame
faces = face_cascade.detectMultiScale(gray, scaleFactor=1.1, minNeighbors=5, minSize=(30, 30))

# Check the number of detected faces
num_faces = len(faces)
# if num_faces > 1:
#     print("Multiple faces detected. Exiting...")
#     # Return the number of faces detected
print(num_faces)


# Perform any additional processing as needed
# For example, you can draw rectangles around the detected faces and save the result to a file

# Cleanup - release resources, if any
