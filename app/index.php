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
        // Generar URL para el QR usando Google Charts API (URL completa)
        $qrData = "Residuo: ".$selectedWaste['name']."\n";
        $qrData .= "Tipo: ".$selectedWaste['type']."\n";
        $qrData .= "Caneca: ".$selectedWaste['color']."\n";
        $qrData .= "Consejo: ".$selectedWaste['tips'];
        
        // Asegúrate de usar la URL completa con https://
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
</head>
<body class="min-h-screen bg-gray-50">
    <header class="bg-green-600 text-white p-4 shadow-md">
        <div class="container mx-auto flex justify-between items-center">
            <div class="flex items-center gap-4">
                <img src="/front/media/logo.png">
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
                    <h2 class="text-xl text-gray-700">Selecciona el residuo que deseas desechar o escríbelo</h2>
                </div>
                
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
                        <p class="text-gray-500">Selecciona o escribe un residuo para ver su información</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
</body>
</html>