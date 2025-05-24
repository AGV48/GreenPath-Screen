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
    <script src="https://cdn.jsdelivr.net/npm/@tensorflow/tfjs@3.18.0/dist/tf.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@tensorflow-models/mobilenet@2.1.0"></script>
</head>
<body class="min-h-screen bg-gray-50">
    <header class="bg-green-600 text-white p-4 shadow-md">
        <div class="container mx-auto flex justify-between items-center">
            <div class="flex items-center gap-4">
                <img src="media/logo.png" width="50" height="50" alt="Logo GREENPATH VISIONS" class="rounded-full">
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
            <!-- Sección de cámara -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="text-center mb-6">
                    <h1 class="text-3xl font-bold text-green-600 mb-2">Desechar Residuo</h1>
                    <h2 class="text-xl text-gray-700">Escanea el residuo con tu cámara o selecciónalo manualmente</h2>
                </div>
                
                <form method="POST" enctype="multipart/form-data" class="space-y-4">
                    <!-- Contenedor de la cámara -->
                    <div class="mb-4">
                        <video id="camera" autoplay playsinline class="w-full rounded-lg border border-gray-300"></video>
                        <button id="captureBtn" class="mt-2 bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                            Capturar y Clasificar
                        </button>
                    </div>
                    
                    <!-- Botones de control de la cámara -->
                    <div class="flex justify-center gap-4">
                        <button type="button" id="takePhotoBtn" class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-full transition duration-200 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Capturar
                        </button>
                        <button type="button" id="retakePhotoBtn" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-3 px-6 rounded-full transition duration-200 flex items-center gap-2 hidden">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Volver a tomar
                        </button>
                        <button type="submit" id="sendPhotoBtn" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-full transition duration-200 flex items-center gap-2 hidden">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                            </svg>
                            Enviar
                        </button>
                    </div>
                    
                    <!-- Input oculto para la foto capturada -->
                    <input type="file" id="photoInput" name="waste_image" accept="image/*" capture="environment" class="hidden">
                    
                    <!-- Campo de texto alternativo (mantenido por si acaso) -->
                    <div class="pt-4 border-t border-gray-200">
                        <label for="waste_name_input" class="block text-center text-gray-700 mb-2">O escribe el nombre del residuo:</label>
                        <div class="flex gap-2">
                            <input type="text" id="waste_name_input" name="waste_name_input" placeholder="Ej: Botella de plástico" class="flex-1 px-4 py-2 rounded-lg border border-gray-300">
                            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                                Buscar
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="text-center mb-6">
                    <h1 class="text-3xl font-bold text-green-600 mb-2">Resultado</h1>
                    <?php if (!empty($selectedWaste['name'])): ?>
                        <h2 class="text-xl text-gray-700 mb-4">Residuo: <?php echo htmlspecialchars($selectedWaste['name']); ?></h2>
                        
                        <div class="flex flex-col items-center space-y-4">
                            <div class="flex items-center justify-center gap-4">
                                <img src="<?php echo $selectedWaste['binImage']; ?>" alt="Caneca" class="w-32 h-32 object-contain" />
                                <?php if (!empty($selectedWaste['qrImage'])): ?>
                                    <img src="<?php echo $selectedWaste['qrImage']; ?>" alt="Código QR" class="w-32 h-32 object-contain border border-gray-200" />
                                <?php endif; ?>
                            </div>
                            
                            <div class="w-full max-w-md bg-gray-100 rounded-lg p-4 space-y-2">
                                <div class="flex justify-between">
                                    <span class="font-semibold">Tipo:</span>
                                    <span><?php echo $selectedWaste['type']; ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="font-semibold">Color de caneca:</span>
                                    <span><?php echo $selectedWaste['color']; ?></span>
                                </div>
                                <div class="pt-2 border-t border-gray-200">
                                    <p class="font-semibold">Consejos:</p>
                                    <p><?php echo $selectedWaste['tips']; ?></p>
                                </div>
                            </div>

                            <?php if (!empty($selectedWaste['qrImage'])): ?>
                                <a href="<?php echo $selectedWaste['qrImage']; ?>" download="qr_<?php echo str_replace(' ', '_', strtolower($selectedWaste['name'])); ?>.png" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                                    Descargar QR
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-gray-500">Captura o sube una imagen de un residuo para identificarlo</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <!-- Script para el reconocimiento de imágenes -->
    <script>
        // Mapeo de clases de residuos a tus categorías (personalízalo según tu modelo)
        const wasteCategories = {
            "plastic": "Botella de Plástico",
            "bottle": "Botella de Plástico",
            "fruit": "Cáscara de Fruta",
            "newspaper": "Periódico",
            "can": "Latas"
        };

        // 1. Acceder a la cámara
        const video = document.getElementById('camera');
        const captureBtn = document.getElementById('captureBtn');
        let model;

        // Cargar el modelo al iniciar
        async function initCamera() {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ video: true });
                video.srcObject = stream;
                
                // Cargar modelo MobileNet
                model = await mobilenet.load();
                console.log("Modelo cargado!");
            } catch (err) {
                console.error("Error al acceder a la cámara:", err);
                alert("No se pudo acceder a la cámara. Asegúrate de permitir los permisos.");
            }
        }

        // 2. Clasificar la imagen capturada
        captureBtn.addEventListener('click', async () => {
            if (!model) {
                alert("El modelo aún no está listo. Espera unos segundos.");
                return;
            }

            // Crear un canvas temporal para capturar el frame
            const canvas = document.createElement('canvas');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

            // Clasificar la imagen
            const predictions = await model.classify(canvas);
            const topPrediction = predictions[0].className.toLowerCase();

            // Buscar coincidencia en wasteCategories
            let matchedWaste = null;
            for (const [key, value] of Object.entries(wasteCategories)) {
                if (topPrediction.includes(key)) {
                    matchedWaste = value;
                    break;
                }
            }

            // Enviar el resultado al formulario
            if (matchedWaste) {
                document.querySelector('input[name="waste_name_input"]').value = matchedWaste;
                // Opcional: enviar automáticamente el formulario
                document.querySelector('button[name="submit_text"]').click();
            } else {
                alert("No se pudo identificar el residuo. Intenta manualmente.");
            }
        });

        // Iniciar al cargar la página
        window.addEventListener('DOMContentLoaded', initCamera);
    </script>
</body>
</html>