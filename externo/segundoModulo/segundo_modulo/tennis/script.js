async function loadModels() {
  await faceapi.nets.ssdMobilenetv1.loadFromUri("../models");
  await faceapi.nets.faceLandmark68Net.loadFromUri("../models");
}

async function detectFaces(image) {
  const detections = await faceapi.detectAllFaces(image).withFaceLandmarks();
  return detections;
}

function calculateExpand(boxes) {
  if (boxes.length < 2) return 600;

  let totalDistance = 0;
  let count = 0;

  for (let i = 0; i < boxes.length - 1; i++) {
    for (let j = i + 1; j < boxes.length; j++) {
      const dx = boxes[i].x - boxes[j].x;
      const dy = boxes[i].y - boxes[j].y;
      const distance = Math.sqrt(dx * dx + dy * dy);
      totalDistance += distance;
      count++;
    }
  }

  const averageDistance = totalDistance / count;

  return Math.max(600 - averageDistance / 2, 100);
}

function calculateZoom(boxes) {
  let faceSizes = boxes.map(box => box.width * box.height);
  let avgFaceSize = faceSizes.reduce((a, b) => a + b, 0) / faceSizes.length;

  const referenceFaceSize = 5000;

  let result = {
    zoom: 0,
    distance: ''
  };

  if (avgFaceSize > referenceFaceSize) {
    result.zoom = -0.008;
    result.distance = 'cerca';
  } else {
    result.zoom = 0.25;
    result.distance = 'lejos';
  }

  return result;
}

function cropImage(image, boxes, expand) {
  const canvas = document.getElementById("canvas");
  const context = canvas.getContext("2d");

  const targetAspectRatio = 16 / 10;
  const targetWidth = 1600;
  const targetHeight = targetWidth / targetAspectRatio;

  let minX = Math.max(0, Math.min(...boxes.map((box) => box.left)) - expand);
  let minY = Math.max(0, Math.min(...boxes.map((box) => box.top)) - expand);
  let maxX = Math.min(image.width, Math.max(...boxes.map((box) => box.right)) + expand);
  let maxY = Math.min(image.height, Math.max(...boxes.map((box) => box.bottom)) + expand);

  let width = maxX - minX;
  let height = maxY - minY;

  const currentAspectRatio = width / height;

  const zoomInfo = calculateZoom(boxes);
  let zoom = zoomInfo.zoom;

  width *= (1 + zoom);
  height *= (1 + zoom);

  if (currentAspectRatio > targetAspectRatio) {
    const newWidth = height * targetAspectRatio;
    const delta = (width - newWidth) / 2;
    minX += delta;
    width = newWidth;
  } else if (currentAspectRatio < targetAspectRatio) {
    const newHeight = width / targetAspectRatio;
    const delta = (height - newHeight) / 2;
    minY += delta;
    height = newHeight;
  }

  canvas.width = targetWidth;
  canvas.height = targetHeight;

  // Ajuste para evitar la distorsión manteniendo el aspecto original
  context.drawImage(image, minX, minY, width, height, 0, 0, canvas.width, canvas.height);

  return {
    croppedImage: canvas.toDataURL(),
    distance: zoomInfo.distance
  };
}

document.getElementById("imageUpload").addEventListener("change", async (event) => {
  const file = event.target.files[0];

  if (!document.body.classList.contains('loading')) {
    document.body.classList.add('loading');
  }

  document.querySelector('.generate_image').disabled = true;

  if (!file) {
    if (document.body.classList.contains('loading')) {
      document.body.classList.remove('loading');
    }
    document.getElementById("imageUpload").value = '';
    return;
  }

  const image = new Image();
  image.src = URL.createObjectURL(file);

  image.onload = async () => {
    const detections = await detectFaces(image);

    document.getElementById("imageContainer").innerHTML = '';

    if (document.querySelector('.uploaded_photo')) {
      document.querySelector('.uploaded_photo').parentElement.removeChild(document.querySelector('.uploaded_photo'));
    }

    if (detections.length > 0) {
      const boxes = detections.map((d) => d.detection.box);
      const expand = calculateExpand(boxes);
      const croppedData = cropImage(image, boxes, expand);
      const imgElement = document.createElement("img");
      imgElement.src = croppedData.croppedImage;

      document.querySelector('.generate_image').disabled = false;
      document.getElementById("imageContainer").appendChild(imgElement);

      const li = document.createElement('li');
      li.classList.add('uploaded_photo');
      li.innerHTML = `
        <svg xmlns="http://www.w3.org/2000/svg" class="ionicon check" viewBox="0 0 512 512">
          <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="32" d="M416 128L192 384l-96-96" />
        </svg>
        <p>Foto cargada correctamente! <br>Se encontraron <b>${detections.length} rostros</b>. Además, se considera(n) <b>${croppedData.distance}</b> del encuadre de la foto.</p>
      `;

      document.querySelector('ul#testing').appendChild(li);

      if (document.body.classList.contains('loading')) {
        document.body.classList.remove('loading');
      }
    } else {

      if (document.body.classList.contains('loading')) {
        document.body.classList.remove('loading');
      }
      document.getElementById("imageUpload").value = '';

      setTimeout(() => {
        alert('No se pudo reconocer alguna cara en la imagen.');
      }, 500);
    }
  };
});

loadModels();