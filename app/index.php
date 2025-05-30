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
    $wasteName = $_POST['waste_name'] ?? $_POST['waste_name_input'] ?? '';
    $normalizedInput = normalizeString($wasteName);

    if (array_key_exists($normalizedInput, $wasteData)) {
        $selectedWaste = $wasteData[$normalizedInput];
        $qrData = json_encode([
    'system' => 'GREENPATH',
    'version' => '1.0',
    'type' => 'waste_disposal',
    'waste_id' => md5($normalizedInput . time()), // ID único
    'waste_name' => $selectedWaste['name'],
    'waste_type' => $selectedWaste['type'],
    'bin_color' => $selectedWaste['color'],
    'timestamp' => time()
]);

    $selectedWaste['qrImage'] = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=".urlencode($qrData);
    } elseif (!empty($wasteName)) {
        $selectedWaste = [
            "name" => ucfirst($wasteName),
            "type" => "Indeterminado",
            "color" => "Negra",
            "tips" => "Consulta las normas locales de reciclaje",
            "binImage" => "front/media/Caneca_Negra.png",
            "qrImage" => ""
        ];
    }
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
    <style>
        .hidden {
            display: none;
        }
        #camera {
            max-height: 300px;
            background: #000;
        }
        #captureBtn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }
        .camera-container {
            position: relative;
        }
        .processing-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;
            font-weight: bold;
        }
    </style>
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
            <!-- Sección de selección -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="text-center mb-6">
                    <h1 class="text-3xl font-bold text-green-600 mb-2">Desechar Residuo</h1>
                    <h2 class="text-xl text-gray-700">Escanea el residuo con tu cámara o selecciónalo manualmente</h2>
                </div>
                
                <div class="camera-container mb-4">
                    <video id="camera" autoplay playsinline class="w-full rounded-lg"></video>
                    <div id="processingOverlay" class="processing-overlay hidden">
                        <p>Procesando imagen...</p>
                    </div>
                </div>
                <button id="captureBtn" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-lg transition duration-200 mb-4">
                    Capturar y Clasificar
                </button>
                
                <form method="POST" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <button type="submit" name="waste_name" value="Botella de Plástico" 
                            class="w-full bg-white border border-green-600 text-green-600 hover:bg-green-600 hover:text-white font-bold py-3 px-4 rounded-lg transition duration-200">
                            Botella de Plástico
                        </button>
                        
                        <button type="submit" name="waste_name" value="Cáscara de Fruta" 
                            class="w-full bg-white border border-green-600 text-green-600 hover:bg-green-600 hover:text-white font-bold py-3 px-4 rounded-lg transition duration-200">
                            Cáscara de Fruta
                        </button>
                        
                        <button type="submit" name="waste_name" value="Periódico" 
                            class="w-full bg-white border border-green-600 text-green-600 hover:bg-green-600 hover:text-white font-bold py-3 px-4 rounded-lg transition duration-200">
                            Periódico
                        </button>
                        
                        <button type="submit" name="waste_name" value="Latas" 
                            class="w-full bg-white border border-green-600 text-green-600 hover:bg-green-600 hover:text-white font-bold py-3 px-4 rounded-lg transition duration-200">
                            Latas
                        </button>
                    </div>
                    
                    <div class="flex gap-2 mt-6">
                        <input type="text" name="waste_name_input" placeholder="Escribe el nombre del residuo" 
                            class="flex-1 px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-green-500 focus:border-transparent transition">
                        <button type="submit" name="submit_text" 
                            class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg transition duration-200">
                            Buscar
                        </button>
                    </div>
                </form>
            </div>

            <!-- Sección de resultados -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="text-center mb-6">
                    <h1 class="text-3xl font-bold text-green-600 mb-2">Resultado</h1>
                    
                    <!-- Resultado de la cámara -->
                    <div id="cameraResult" class="hidden">
                        <h2 class="text-xl text-gray-700 mb-4">Residuo: <span id="detectedWasteName"></span></h2>
                        <div class="flex flex-col items-center space-y-4">
                            <div class="flex items-center justify-center gap-4">
                                <img id="detectedBinImage" src="" alt="Caneca" class="w-32 h-32 object-contain" />
                                <img id="detectedQrImage" src="" alt="Código QR" class="w-32 h-32 object-contain border border-gray-200 hidden" />
                            </div>
                            
                            <div class="w-full max-w-md bg-gray-100 rounded-lg p-4 space-y-2">
                                <div class="flex justify-between">
                                    <span class="font-semibold">Tipo:</span>
                                    <span id="detectedWasteType"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="font-semibold">Color de caneca:</span>
                                    <span id="detectedWasteColor"></span>
                                </div>
                                <div class="pt-2 border-t border-gray-200">
                                    <p class="font-semibold">Consejos:</p>
                                    <p id="detectedWasteTips"></p>
                                </div>
                            </div>

                            <a id="downloadQrLink" href="#" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200 hidden">
                                Descargar QR
                            </a>
                        </div>
                    </div>
                    
                    <!-- Resultado del formulario PHP -->
                    <?php if (!empty($selectedWaste['name'])): ?>
                        <div id="formResult">
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
                        </div>
                    <?php else: ?>
                        <p id="noResultMessage" class="text-gray-500">Selecciona o escribe un residuo para ver su información</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Mapeo de clases de MobileNet a tus categorías de residuos
        const wasteMap = {
            "water bottle": "botella de plastico",
            "plastic bag": "botella de plastico",
            "banana": "cascara de fruta",
            "orange": "cascara de fruta",
            "newspaper": "periodico",
            "envelope": "periodico",
            "can": "latas",
            "beer can": "latas"
        };

        // Elementos del DOM
        const video = document.getElementById('camera');
        const captureBtn = document.getElementById('captureBtn');
        const processingOverlay = document.getElementById('processingOverlay');
        const cameraResultSection = document.getElementById('cameraResult');
        const formResultSection = document.getElementById('formResult');
        const noResultMessage = document.getElementById('noResultMessage');
        
        // Elementos de resultado
        const detectedWasteName = document.getElementById('detectedWasteName');
        const detectedBinImage = document.getElementById('detectedBinImage');
        const detectedQrImage = document.getElementById('detectedQrImage');
        const detectedWasteType = document.getElementById('detectedWasteType');
        const detectedWasteColor = document.getElementById('detectedWasteColor');
        const detectedWasteTips = document.getElementById('detectedWasteTips');
        const downloadQrLink = document.getElementById('downloadQrLink');

        // Datos de residuos desde PHP
        const wasteData = <?php echo json_encode($wasteData); ?>;
        const defaultWaste = {
            name: "Desconocido",
            type: "Indeterminado",
            color: "Negra",
            tips: "Consulta las normas locales de reciclaje",
            binImage: "front/media/Caneca_Negra.png"
        };

        let model;

        // 1. Iniciar cámara y cargar modelo
        async function initCamera() {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ video: true });
                video.srcObject = stream;
                model = await mobilenet.load();
                console.log("Modelo cargado correctamente");
            } catch (err) {
                console.error("Error:", err);
                alert("Error al acceder a la cámara o cargar el modelo");
            }
        }

        // 2. Función para clasificar residuos
        captureBtn.addEventListener('click', async () => {
            if (!model) {
                alert("El modelo no está listo. Intenta de nuevo.");
                return;
            }

            // Mostrar loader
            captureBtn.disabled = true;
            processingOverlay.classList.remove('hidden');

            try {
                // Capturar frame de la cámara
                const canvas = document.createElement('canvas');
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(video, 0, 0);

                // Clasificar la imagen
                const predictions = await model.classify(canvas);
                console.log("Predicciones:", predictions);

                // Buscar coincidencia en nuestro mapeo
                let detectedWasteKey = null;
                for (const prediction of predictions) {
                    for (const [key, value] of Object.entries(wasteMap)) {
                        if (prediction.className.toLowerCase().includes(key.toLowerCase())) {
                            detectedWasteKey = value;
                            break;
                        }
                    }
                    if (detectedWasteKey) break;
                }

                // Obtener datos del residuo
                const wasteInfo = detectedWasteKey ? wasteData[detectedWasteKey] : defaultWaste;

                // Actualizar la interfaz
                detectedWasteName.textContent = wasteInfo.name;
                detectedBinImage.src = wasteInfo.binImage;
                detectedWasteType.textContent = wasteInfo.type;
                detectedWasteColor.textContent = wasteInfo.color;
                detectedWasteTips.textContent = wasteInfo.tips;

                // Mostrar/ocultar QR si es conocido
                if (detectedWasteKey) {
                    const qrData = `Residuo: ${wasteInfo.name}\nTipo: ${wasteInfo.type}\nCaneca: ${wasteInfo.color}\nConsejo: ${wasteInfo.tips}`;
                    const qrImageUrl = `https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${encodeURIComponent(qrData)}`;
                    detectedQrImage.src = qrImageUrl;
                    detectedQrImage.classList.remove('hidden');
                    downloadQrLink.href = qrImageUrl;
                    downloadQrLink.setAttribute('download', `qr_${wasteInfo.name.replace(/ /g, '_')}.png`);
                    downloadQrLink.classList.remove('hidden');
                } else {
                    detectedQrImage.classList.add('hidden');
                    downloadQrLink.classList.add('hidden');
                }

                // Mostrar resultados
                cameraResultSection.classList.remove('hidden');
                if (formResultSection) formResultSection.classList.add('hidden');
                if (noResultMessage) noResultMessage.classList.add('hidden');
                
                console.log("Residuo detectado:", wasteInfo);
            } catch (error) {
                console.error("Error:", error.message);
                // Mostrar error como residuo desconocido
                detectedWasteName.textContent = defaultWaste.name;
                detectedBinImage.src = defaultWaste.binImage;
                detectedWasteType.textContent = defaultWaste.type;
                detectedWasteColor.textContent = defaultWaste.color;
                detectedWasteTips.textContent = defaultWaste.tips;
                detectedQrImage.classList.add('hidden');
                downloadQrLink.classList.add('hidden');
                
                cameraResultSection.classList.remove('hidden');
                if (formResultSection) formResultSection.classList.add('hidden');
                if (noResultMessage) noResultMessage.classList.add('hidden');
            } finally {
                captureBtn.disabled = false;
                processingOverlay.classList.add('hidden');
            }
        });

        // Iniciar cuando la página cargue
        window.addEventListener('DOMContentLoaded', initCamera);
    </script>
</body>
</html>