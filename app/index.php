<?php
function normalizeString($string) {
    $search = ['á','é','í','ó','ú','ü','ñ'];
    $replace = ['a','e','i','o','u','u','n'];
    $string = strtolower(trim($string));
    return str_replace($search, $replace, $string);
}

$wasteData = [
    "botella de plastico" => [
        "name" => "Botella de Plástico",
        "type" => "Plástico",
        "color" => "Blanca",
        "tips" => "Lávala antes de desecharla para evitar olores",
        "binImage" => "front/media/Caneca_Blanca.png"
    ],
    "cascara de fruta" => [
        "name" => "Cáscara de Fruta",
        "type" => "Orgánico",
        "color" => "Verde",
        "tips" => "Puedes compostarla si tienes espacio",
        "binImage" => "front/media/Caneca_Verde.png"
    ],
    "periodico" => [
        "name" => "Periódico",
        "type" => "Papel",
        "color" => "Azul",
        "tips" => "Dobla los periódicos para ocupar menos espacio",
        "binImage" => "front/media/Caneca_Azul.png"
    ],
    "latas" => [
        "name" => "Latas",
        "type" => "Metal",
        "color" => "Gris",
        "tips" => "Aplástalas para ahorrar espacio",
        "binImage" => "front/media/Caneca_Gris.png"
    ]
];

$selectedWaste = [
    "name" => "",
    "type" => "",
    "color" => "",
    "tips" => "",
    "binImage" => "front/media/Caneca_Negra.png",
    "qrImage" => ""
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Procesamiento de imagen si se envió
    if (isset($_FILES['waste_image']) && $_FILES['waste_image']['error'] === UPLOAD_ERR_OK) {
        // Aquí implementaremos el reconocimiento
        $recognizedWaste = recognizeWaste($_FILES['waste_image']);
        
        $normalizedInput = normalizeString($recognizedWaste);
        
        if (array_key_exists($normalizedInput, $wasteData)) {
            $selectedWaste = $wasteData[$normalizedInput];
            // Generar QR
            $qrData = "Residuo: ".$selectedWaste['name']."\n";
            $qrData .= "Tipo: ".$selectedWaste['type']."\n";
            $qrData .= "Caneca: ".$selectedWaste['color']."\n";
            $qrData .= "Consejo: ".$selectedWaste['tips'];
            
            $selectedWaste['qrImage'] = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=".urlencode($qrData);
        } else {
            $selectedWaste = [
                "name" => ucfirst($recognizedWaste),
                "type" => "Indeterminado",
                "color" => "Negra",
                "tips" => "Consulta las normas locales de reciclaje",
                "binImage" => "front/media/Caneca_Negra.png",
                "qrImage" => ""
            ];
        }
    }
    // [Mantén el resto del código de procesamiento...]
}

// Función para reconocer residuos (simulada - implementación real requeriría IA)
function recognizeWaste($imageFile) {
    // En un sistema real, aquí integrarías con un modelo de ML o API de reconocimiento
    // Esta es una simulación básica que "reconoce" basado en el nombre del archivo
    
    $recognitions = [
        'botella' => 'Botella de Plástico',
        'plastico' => 'Botella de Plástico',
        'cascara' => 'Cáscara de Fruta',
        'fruta' => 'Cáscara de Fruta',
        'periodico' => 'Periódico',
        'papel' => 'Periódico',
        'lata' => 'Latas',
        'metal' => 'Latas'
    ];
    
    $fileName = strtolower($imageFile['name']);
    
    foreach ($recognitions as $key => $value) {
        if (strpos($fileName, $key) !== false) {
            return $value;
        }
    }
    
    return 'Indeterminado';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Desechar Residuo - GREENPATH VISIONS</title>
    <link rel="stylesheet" href="front/css/styles.css">
    <link rel="shortcut icon" href="front/media/logo.png" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gray-50">
    <header class="bg-green-600 text-white p-4 shadow-md">
        <div class="container mx-auto flex justify-between items-center">
            <div class="flex items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold">GREENPATH</h1>
                    <h2 class="text-xl">VISIONS</h2>
                </div>
            </div>
            <h1 class="text-xl font-semibold">Sistema de Clasificación de Residuos</h1>
        </div>
    </header>

    <main class="container mx-auto p-4">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mt-8">
        <!-- Sección de cámara (mantén igual que antes)... -->
        
        <!-- Sección de resultados mejorada -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="text-center mb-6">
                <h1 class="text-3xl font-bold text-green-600 mb-2">Resultado</h1>
                <?php if (!empty($selectedWaste['name'])): ?>
                    <h2 class="text-xl text-gray-700 mb-4">Residuo: <?php echo htmlspecialchars($selectedWaste['name']); ?></h2>
                    
                    <div class="flex flex-col items-center space-y-6">
                        <!-- Contenedor principal de resultado -->
                        <div class="w-full bg-<?php echo strtolower($selectedWaste['color']); ?>-100 rounded-xl p-6 shadow-inner">
                            <div class="flex flex-col md:flex-row items-center justify-center gap-6">
                                <!-- Imagen de la caneca -->
                                <div class="text-center">
                                    <img src="<?php echo $selectedWaste['binImage']; ?>" 
                                         alt="Caneca <?php echo $selectedWaste['color']; ?>" 
                                         class="w-40 h-40 object-contain mx-auto">
                                    <p class="mt-2 font-bold text-lg">Caneca <?php echo $selectedWaste['color']; ?></p>
                                </div>
                                
                                <!-- Información detallada -->
                                <div class="bg-white rounded-lg p-4 shadow-md flex-1 max-w-md">
                                    <div class="space-y-3">
                                        <div>
                                            <p class="font-semibold text-gray-700">Tipo de residuo:</p>
                                            <p class="text-lg"><?php echo $selectedWaste['type']; ?></p>
                                        </div>
                                        <div>
                                            <p class="font-semibold text-gray-700">Recomendación:</p>
                                            <p class="text-green-600"><?php echo $selectedWaste['tips']; ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Código QR -->
                        <?php if (!empty($selectedWaste['qrImage'])): ?>
                        <div class="text-center mt-4">
                            <p class="text-sm text-gray-500 mb-2">Escanea este código para más información</p>
                            <img src="<?php echo $selectedWaste['qrImage']; ?>" 
                                 alt="Código QR" 
                                 class="w-32 h-32 object-contain border border-gray-200 mx-auto">
                            <a href="<?php echo $selectedWaste['qrImage']; ?>" 
                               download="qr_<?php echo str_replace(' ', '_', strtolower($selectedWaste['name'])); ?>.png" 
                               class="inline-block mt-2 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                                Descargar QR
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="py-12">
                        <img src="front/media/scan_instruction.png" alt="Instrucciones" class="w-48 mx-auto mb-4">
                        <p class="text-gray-500">Captura una imagen del residuo para identificar el contenedor correcto</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const video = document.getElementById('cameraPreview');
            const canvas = document.getElementById('photoCanvas');
            const takePhotoBtn = document.getElementById('takePhotoBtn');
            const retakePhotoBtn = document.getElementById('retakePhotoBtn');
            const sendPhotoBtn = document.getElementById('sendPhotoBtn');
            const noCameraMessage = document.getElementById('noCameraMessage');
            const photoInput = document.getElementById('photoInput');
            const fileUpload = document.getElementById('fileUpload');
            const form = document.querySelector('form');
            
            let stream = null;
            let photoTaken = false;

            // Iniciar la cámara
            function startCamera() {
                if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                    navigator.mediaDevices.getUserMedia({ 
                        video: { 
                            facingMode: 'environment', // Usar cámara trasera
                            width: { ideal: 1280 },
                            height: { ideal: 720 }
                        } 
                    })
                    .then(function(mediaStream) {
                        stream = mediaStream;
                        video.srcObject = mediaStream;
                        video.style.display = 'block';
                        noCameraMessage.style.display = 'none';
                    })
                    .catch(function(error) {
                        console.error("Error al acceder a la cámara:", error);
                        video.style.display = 'none';
                        noCameraMessage.style.display = 'flex';
                    });
                } else {
                    console.error("getUserMedia no soportado");
                    video.style.display = 'none';
                    noCameraMessage.style.display = 'flex';
                }
            }

            // Tomar foto
            takePhotoBtn.addEventListener('click', function() {
                if (!photoTaken) {
                    const context = canvas.getContext('2d');
                    canvas.width = video.videoWidth;
                    canvas.height = video.videoHeight;
                    context.drawImage(video, 0, 0, canvas.width, canvas.height);
                    
                    // Detener la cámara
                    if (stream) {
                        stream.getTracks().forEach(track => track.stop());
                    }
                    
                    // Mostrar la foto tomada
                    video.style.display = 'none';
                    canvas.style.display = 'block';
                    photoTaken = true;
                    
                    // Cambiar botones
                    takePhotoBtn.classList.add('hidden');
                    retakePhotoBtn.classList.remove('hidden');
                    sendPhotoBtn.classList.remove('hidden');
                }
            });

            // Volver a tomar foto
            retakePhotoBtn.addEventListener('click', function() {
                canvas.style.display = 'none';
                photoTaken = false;
                
                // Cambiar botones
                takePhotoBtn.classList.remove('hidden');
                retakePhotoBtn.classList.add('hidden');
                sendPhotoBtn.classList.add('hidden');
                
                // Reiniciar cámara
                startCamera();
            });

            // Enviar foto
            sendPhotoBtn.addEventListener('click', function() {
                // Convertir canvas a blob y enviarlo
                canvas.toBlob(function(blob) {
                    const file = new File([blob], 'waste_photo.jpg', { type: 'image/jpeg' });
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(file);
                    photoInput.files = dataTransfer.files;
                    
                    // Enviar el formulario
                    form.submit();
                }, 'image/jpeg', 0.9);
            });

            // Manejar cambio en el input de subir archivo
            fileUpload.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    form.submit();
                }
            });

            // Iniciar cámara al cargar la página
            startCamera();
        });
    </script>
</body>
</html>