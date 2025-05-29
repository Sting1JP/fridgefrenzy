<?php
require_once('/var/www/html/blocks/session.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cold Storage Queen: Compliance Audit 🧐</title>
    <style>
        body {
            font-family: 'Comic Sans MS', 'Chalkboard SE', 'Marker Felt', sans-serif;
            background: linear-gradient(135deg, #A8B2C5 0%, #CAD2E0 50%, #A8B2C5 100%); /* More serious, "official" gradient */
            color: #333A45;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            width: 100vw;
            margin: 0;
            overflow: hidden;
            text-align: center;
            box-sizing: border-box;
        }

        #gameHost {
            position: relative;
            width: 95vw;      
            height: 95vh;     
            max-width: 1200px; 
            max-height: 800px; 
            background-color: #E0E6F0; /* Lighter, more clinical */
            border-radius: 25px;
            box-shadow: 0 15px 30px rgba(0,0,0,0.2), inset 0 0 20px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
            justify-content: flex-end; /* UI at the bottom */
            box-sizing: border-box;
            overflow: hidden; 
        }

        canvas#gameCanvas {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 10;
            background-color: transparent;
            display: block;
            cursor: grab; 
        }

        #gameUiContainer {
            width: 100%;
            padding: 10px 20px 20px 20px; 
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 15px; 
            position: relative;
            z-index: 1; /* Below canvas for item falling over it, above for interaction */
        }

        #binsContainer {
            display: flex;
            justify-content: space-around; 
            align-items: flex-end; 
            width: 100%;
            gap: 20px; /* More space for fewer bins */
            flex-shrink: 0;
            margin-top: 10px; 
        }

        .bin {
            flex-grow: 1; 
            min-width: 280px; 
            max-width: 45%; 
            padding: 20px 15px; 
            border-radius: 15px 15px 0 0; 
            font-weight: bold;
            font-size: clamp(1em, 1.8vw, 1.4em); 
            cursor: default;
            transition: transform 0.2s ease-out, box-shadow 0.2s ease-out, background-color 0.2s;
            box-shadow: 0 5px 8px rgba(0,0,0,0.1), inset 0 2px 4px rgba(0,0,0,0.05);
            text-align: center;
            background-clip: padding-box;
            border-top: 5px solid transparent; 
            min-height: 85px; 
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .bin:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 10px 15px rgba(0,0,0,0.2), inset 0 3px 6px rgba(0,0,0,0.08);
        }
        
        #bin-keep { background-color: #E8F5E9; color: #1B5E20; border-bottom: 5px solid #A5D6A7; }
        #bin-keep:hover { border-top-color: #66BB6A; background-color: #C8E6C9;}

        #bin-discard { background-color: #FFEBEE; color: #B71C1C; border-bottom: 5px solid #FFCDD2; }
        #bin-discard:hover { border-top-color: #E57373; background-color: #FFCDD2;}

        .bin.drag-over {
            transform: translateY(-8px) scale(1.05) !important;
            box-shadow: 0 0 20px rgba(173, 216, 230, 0.9), /* Light blue glow */
                        0 12px 20px rgba(0,0,0,0.25), 
                        inset 0 3px 6px rgba(0,0,0,0.1);
        }
        #bin-keep.drag-over { border-top-color: #388E3C !important; }
        #bin-discard.drag-over { border-top-color: #D32F2F !important; }
        

        #messageArea {
            padding: 12px 18px; 
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            font-size: clamp(0.9em, 1.7vw, 1.2em); 
            color: #5D4037; /* Brownish for messages */
            max-width: 90%;
            width: 600px; 
            box-shadow: 0 5px 10px rgba(0,0,0,0.12);
            line-height: 1.5;
            flex-shrink: 0;
            min-height: 60px;
        }
        
        #introScreen {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: linear-gradient(135deg, rgba(168, 178, 197, 0.97) 0%, rgba(202, 210, 224, 0.97) 50%, rgba(168, 178, 197, 0.97) 100%);
            z-index: 1000; display: flex; flex-direction: column; 
            align-items: center; justify-content: center; padding: 20px;
            box-sizing: border-box; text-align: center; overflow: hidden;
        }

        .intro-content-centered {
            background-color: rgba(255, 255, 255, 0.98); padding: 30px;
            border-radius: 30px; box-shadow: 0 15px 40px rgba(80, 40, 40, 0.2);
            max-width: 750px; width: 90%; color: #4A3B34;
            display: flex; flex-direction: column; align-items: center; gap: 15px;
        }

        #introImage {
            max-width: 280px; width: 50%; max-height: 250px; height: auto;
            border-radius: 15px;
        }
        
        .intro-text-block h1 {
            color: #4A5568; /* Darker Blue/Grey */
            font-size: clamp(1.8em, 4vw, 2.3em); margin-bottom: 5px; margin-top: 0;
        }
        
        .intro-text-block h2 {
            color: #718096; /* Medium Grey */
            font-size: clamp(1.3em, 3vw, 1.6em); margin-top: 0; margin-bottom: 15px;
        }

        .intro-text-block p {
            font-size: clamp(0.9em, 1.8vw, 1.1em); line-height: 1.55;
            margin-bottom: 10px; color: #5D4037;
        }

        .intro-text-block p.emphasis-text {
            font-size: clamp(1em, 2.2vw, 1.2em); font-weight: bold;
            color: #2C5282; /* Corporate Blue */ margin: 15px 0;
        }

        .intro-text-block p strong { color: #2D3748; font-weight: bold; }

        #startButton {
            margin-top: 15px; background-color: #4A5568; /* Button matches h1 */
            color: white; padding: 15px 35px; border: none; border-radius: 12px;
            font-size: clamp(1.1em, 2.3vw, 1.4em); cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
            font-family: 'Comic Sans MS', 'Chalkboard SE', 'Marker Felt', sans-serif;
            box-shadow: 0 5px 8px rgba(0,0,0,0.15);
        }
        #startButton:hover { 
            background-color: #2D3748; /* Darker shade */
            transform: scale(1.05) translateY(-2px);
            box-shadow: 0 8px 12px rgba(0,0,0,0.2);
        }

        #gameTopHud {
            position: absolute; top: 20px; left: 20px; right: 20px;
            display: flex; justify-content: space-between; align-items: center;
            z-index: 20; pointer-events: none; font-family: inherit;
            font-size: clamp(1em, 2vw, 1.3em); color: #4A5568; font-weight: bold;
        }
        #currentDayDisplay { /* Placeholder for potential day counter */
             /* text-align: left; */
        }


        /* Side Panels for Directory and Manual */
        .side-panel {
            position: fixed;
            top: 0;
            height: 100%;
            width: 350px; /* Adjust as needed */
            max-width: 90vw;
            background-color: #F7FAFC;
            box-shadow: -5px 0 15px rgba(0,0,0,0.2);
            z-index: 1001; /* Above game host, below intro if needed */
            transform: translateX(100%); /* Start off-screen to the right */
            transition: transform 0.3s ease-in-out;
            padding: 20px;
            box-sizing: border-box;
            overflow-y: auto;
            color: #2D3748;
        }
        .side-panel.left-panel {
            left: 0;
            transform: translateX(-100%);
        }
        .side-panel.left-panel.active {
            transform: translateX(0);
        }
        .side-panel.right-panel {
            right: 0;
            transform: translateX(100%);
        }
        .side-panel.right-panel.active {
            transform: translateX(0);
        }

        .side-panel h2 {
            color: #4A5568;
            margin-top: 0;
            border-bottom: 2px solid #CBD5E0;
            padding-bottom: 10px;
        }
        .side-panel .close-panel-button {
            background-color: #718096; color: white;
            border: none; padding: 8px 15px; border-radius: 5px;
            cursor: pointer; margin-top: 20px; float: right;
        }
        .side-panel .close-panel-button:hover { background-color: #4A5568; }

        #directoryContent .user-profile, #manualContent .rule-section {
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px dashed #E2E8F0;
        }
         #directoryContent .user-profile:last-child, #manualContent .rule-section:last-child {
            border-bottom: none;
        }
        #directoryContent h3, #manualContent h3 { color: #2C5282; margin-bottom: 5px;}
        #directoryContent p, #manualContent p, #directoryContent ul, #manualContent ul {
            font-size: 0.9em; line-height: 1.6; margin-top: 5px;
        }
        #directoryContent strong, #manualContent strong { color: #4A5568;}

        #utilityButtonsContainer {
            position: absolute;
            top: 20px; /* Moved to top right */
            right: 20px;
            z-index: 30; /* Above canvas, below panels when open */
            display: flex;
            gap: 10px;
        }
        #utilityButtonsContainer button {
            background-color: #A0AEC0; color: white;
            font-family: 'Comic Sans MS', 'Chalkboard SE', 'Marker Felt', sans-serif;
            padding: 10px 18px; border: none; border-radius: 8px;
            font-size: clamp(0.8em, 1.5vw, 1em); cursor: pointer;
            box-shadow: 0 3px 5px rgba(0,0,0,0.1);
            transition: background-color 0.2s, transform 0.1s;
        }
        #utilityButtonsContainer button:hover {
            background-color: #718096;
            transform: translateY(-1px);
        }
        #todaysDateDisplay {
            font-size: 0.9em; color: #785549; margin-bottom: 5px;
            align-self: flex-start; /* Align to the left within gameUiContainer */
            padding-left: 10px; /* Some padding if needed */
        }


        @keyframes itemPop {
          0% { transform: scale(1) rotate(0deg); }
          30% { transform: scale(1.15) rotate(1deg); }
          60% { transform: scale(0.98) rotate(-1deg); }
          100% { transform: scale(1) rotate(0deg); }
        }
        .item-pop { animation: itemPop 0.4s ease-out; }

    </style>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Schoolbell&display=swap" rel="stylesheet">
</head>
<body>

    <div id="introScreen">
        <div class="intro-content-centered">
            <img src="splash.png" alt="Cold Storage Queen - Auditor" id="introImage">
            <div class="intro-text-block">
                <h1>Greetings, Trainee Auditor.</h1>
                <h2>The Cold Storage Queen requires... precision.</h2>
                <p>Forget the frenzy. Your new role is critical: <strong>Fridge Compliance Auditor</strong>. This isn't just about tidiness; it's about *rules*. Each item, each label, each expiration date tells a story. Or a lie.</p>
                <p>Your mission: Scrutinize every piece of questionable culinary content. Consult the <strong>User Directory</strong> for employee habits and the <strong>Policy Manual</strong> for the unwavering law of this land (of leftovers).</p>
                <p class="emphasis-text">🚨 LABELING ERRORS. EXPIRATION MYSTERIES. POLICY VIOLATIONS. 🚨</p>
                <p>Sort wisely. The Queen is watching. Good luck. You'll need it. 🧐</p>
            </div>
            <button id="startButton">Begin Audit</button>
        </div>
    </div>

    <div id="gameHost" style="display: none;">
        <canvas id="gameCanvas"></canvas>
        <div id="gameTopHud">
            <div id="currentDayDisplay">Audit Day: 1</div>
            <!-- Utility buttons will be in their own container -->
        </div>
         <div id="utilityButtonsContainer">
            <button id="toggleDirectoryButton">User Directory</button>
            <button id="toggleManualButton">Policy Manual</button>
        </div>
        <div id="gameUiContainer">
            <div id="todaysDateDisplay">Today: MM/DD/YYYY</div>
            <div id="messageArea">Examine the item. Consult your references. Make the call.</div>
            <div id="binsContainer">
                <div class="bin" id="bin-keep" data-bin-type="Keep"><span>✔️ Keep Item</span></div>
                <div class="bin" id="bin-discard" data-bin-type="Discard"><span>❌ Discard Item</span></div>
            </div>
        </div>
    </div>

    <!-- User Directory Panel -->
    <div id="userDirectoryPanel" class="side-panel right-panel"> <!-- Added right-panel -->
        <h2>User Directory</h2>
        <div id="directoryContent">
            <!-- Profiles will be populated by JS -->
        </div>
        <button class="close-panel-button" data-panel-id="userDirectoryPanel">Close</button>
    </div>

    <!-- Manual Panel -->
    <div id="manualPanel" class="side-panel left-panel"> <!-- Added left-panel -->
        <h2>Fridge Policy Manual</h2>
        <div id="manualContent">
            <!-- Rules will be populated by JS -->
        </div>
        <button class="close-panel-button" data-panel-id="manualPanel">Close</button>
    </div>
    
    <!-- No Game Over screen in this version, or it's an "End of Day Report" (not implemented yet) -->

    <script src="/trophies/trophies.js"></script> <!-- Assuming this is for external trophy system -->

    <script>
        // --- CORE GAME ELEMENTS ---
        const gameHost = document.getElementById('gameHost');
        const canvas = document.getElementById('gameCanvas');
        const ctx = canvas.getContext('2d');
        const messageArea = document.getElementById('messageArea');
        const binsElements = {
            Keep: document.getElementById('bin-keep'),
            Discard: document.getElementById('bin-discard')
        };
        const introScreen = document.getElementById('introScreen');
        const startButton = document.getElementById('startButton');
        const todaysDateDisplay = document.getElementById('todaysDateDisplay');
        const currentDayDisplay = document.getElementById('currentDayDisplay'); // For future use

        // --- UI PANELS ---
        const userDirectoryPanel = document.getElementById('userDirectoryPanel');
        const manualPanel = document.getElementById('manualPanel');
        const toggleDirectoryButton = document.getElementById('toggleDirectoryButton');
        const toggleManualButton = document.getElementById('toggleManualButton');
        const directoryContent = document.getElementById('directoryContent');
        const manualContent = document.getElementById('manualContent');
        
        // --- GAME STATE ---
        let currentItem = null;
        let assetsLoaded = false;
        let gameDay = 1; // For potential future progression
        let itemsProcessedThisDay = 0;
        const ITEMS_PER_DAY = 10; // Example: how many items before "end of day"
        let today = new Date(); // Set once at game start for consistency within a "day"

        // --- ASSETS ---
        // milkImage is a fallback, main images are in itemImages
        let milkImage = new Image(); 
        milkImage.src = 'milk.png'; 
        const itemImages = {}; // Store all preloaded item images
        const popSound = new Audio('audio/pop.mp3?v=1'); // Re-purpose for item spawn
        const correctSortSound = new Audio('audio/ding.mp3'); // For correct decisions
        const incorrectSortSound = new Audio('audio/buzz.mp3'); // For incorrect decisions
        const bgMusic = new Audio('audio/fridgemusic_ambient.mp3'); // Suggesting a more ambient track

        // --- CONFIGURATIONS ---
        const ITEM_ORIGINAL_WIDTH = 1024; 
        const ITEM_ORIGINAL_HEIGHT = 1024;
        const ITEM_SCALE_VS_CANVAS_WIDTH = 0.28; // Slightly smaller items maybe
        const POST_IT_SCALE_VS_ITEM_DIMENSION = 0.4;
        const POST_IT_FONT_SCALE_VS_LABEL_HEIGHT = 0.16;
        const MIN_POST_IT_FONT_SIZE = 13;
        const MAX_POST_IT_FONT_SIZE = 15;
        const LABEL_FONT_SIZE_FALLBACK = 16;
        const labelTextColors = ['#000000', '#00008B', '#4B0082']; // Can keep or simplify

        let currentlyOverlappedBinType = null;


        // --- PEOPLE PROFILES & RULES ---
        const PEOPLE_PROFILES = {
            "fran": {
                id: "fran",
                name: "Fran",
                title: "Manager",
                portrait: "portraits/fran.png", // Create this image
                bio: "Fran is French. He's generally good with labels but sometimes uses French date conventions or abbreviates.",
                // How Fran *actually* labels items
                labelingHabits: {
                    nameAccuracy: 0.95, 
                    dateAccuracy: 0.85, // 85% chance labeled date is close to actual
                    usesPreferredFormat: 1,
                    preferredDateFormat: "DD/MM/YY", // e.g., 25/12/23
                    alternativeDateFormats: ["D MMM YY"],
                    signatureChance: 1,
                    signatureStyle: "-F." 
                },
                specialRules: [
                    { type: "item_specific", item_id: "wine", rule: "Allowed to keep open wine for up to 3 days. Check vintage on label if old." },
                    { type: "behavior", note: "Tends to forget about bananas." }
                ]
            },
            "kenzo": {
                id: "kenzo",
                name: "Kenzo",
                title: "CEO",
                portrait: "portraits/kenzo.png", // Create this image
                bio: "The CEO. Expects perfection. His items are usually meticulously labeled. Dislikes clutter.",
                labelingHabits: {
                    nameAccuracy: 1.0,
                    dateAccuracy: 0.98,
                    usesPreferredFormat: 1.0,
                    preferredDateFormat: "YYYY.MM.DD",
                    signatureChance: 0.8, // 80% chance of using signature if name is accurate
                    signatureStyle: "Kenzo" 
                },
                specialRules: [
                    { type: "priority_owner", rule: "CEO's items: Do NOT discard unless *explicitly* spoiled (visually confirmed) AND past labeled date. If in doubt, KEEP." },
                    { type: "preference", item_id: "sushi", note: "Often brings expensive sushi. Must be fresh."}
                ]
            },
            "greg": {
                id: "greg",
                name: "Greg Smith",
                title: "Accounting",
                portrait: "portraits/greg.png", // Create this image
                bio: "Greg is... Greg. Often forgets labels entirely or writes 'For Greg'. Dates are a mystery, sometimes just 'Monday'.",
                labelingHabits: {
                    nameAccuracy: 0.6, // Often forgets name or just writes "Mine"
                    dateAccuracy: 0.5, // Dates are often wildly off or missing
                    usesPreferredFormat: 0.4, // Format is inconsistent
                    preferredDateFormat: "M/D/Y", // e.g., 1/5/24 or 12/25/2023
                    alternativeDateFormats: ["ddd", "MMM D"], // e.g. "Mon", "Jan 5"
                    writesVagueDate: 0.3,
                    signatureChance: 0.5, // Sometimes uses a nickname/signature
                    signatureStyle: "Greggy!"
                },
                specialRules: [
                    { type: "common_item", item_id: "pizza", note: "Frequently leaves leftover pizza. Policy: discard after 2 days regardless of label if any." },
                    { type: "date", item_id: "pizza", note: "Greg gets a pass on writing dates in strange ways because... he's Greg." }
                ]
            }
        };

        const GENERAL_RULES = [
            { id: "GR001", title: "Basic Labeling", text: "All personal items stored in the communal fridge MUST be clearly labeled with the owner's full name and a disposal date (DD/MM/YY or YYYY-MM-DD preferred)." },
            { id: "GR002", title: "Unlabeled Items", text: "Perishable items found without a clear name AND date will be held for 24 hours in the 'Lost & Found Cold Box' (not in this game yet!), then discarded. Non-perishables held for 48 hours." },
            { id: "GR003", title: "Expiration Policy", text: "Items past their labeled disposal date, or deemed visually/olfactorily spoiled, will be discarded immediately, regardless of label. Standard shelf life for unlabeled but identified items: Dairy (3 days), Cooked Meat/Fish (2 days), Produce (5 days), Leftovers (2 days)." },
            { id: "GR004", title: "CEO Exception", text: "Refer to CEO Kenzo's profile for specific handling of his items. Extreme caution advised." },
            { id: "GR005", title: "Visual Spoilage", text: "If an item shows clear signs of spoilage (mold, unusual discoloration, off-putting odor - auditor uses visual only), it should be discarded even if the label indicates it's fresh. Common sense prevails."}
        ];

        const possibleItems = [ // Base item types
            { id: "milk", displayName: "Milk", imageSrc: "milk.png", category: "dairy", baseShelfLifeDays: 3 },
            { id: "sushi", displayName: "Sushi", imageSrc: "sushi.png", category: "fish", baseShelfLifeDays: 1 },
            { id: "yogurt", displayName: "Yogurt", imageSrc: "yogurt.png", category: "dairy", baseShelfLifeDays: 5 },
            { id: "pepsi", displayName: "Pepsi", imageSrc: "pepsi.png", category: "drink", baseShelfLifeDays: 365, nonPerishable: true },
            { id: "banana", displayName: "Banana", imageSrc: "banana.png", category: "produce", baseShelfLifeDays: 4 },
            { id: "salad", displayName: "Salad", imageSrc: "salad.png", category: "produce", baseShelfLifeDays: 3 },
            { id: "whiskey", displayName: "Whiskey", imageSrc: "whiskey.png", category: "drink", baseShelfLifeDays: 1000, nonPerishable: true },
            { id: "pizza", displayName: "Pizza Slice", imageSrc: "pizza.png", category: "leftovers", baseShelfLifeDays: 2 },
            { id: "wine", displayName: "Wine Bottle", imageSrc: "wine.png", category: "drink", baseShelfLifeDays: 730, nonPerishableUntilOpened: true },
            { id: "cheese", displayName: "Cheese", imageSrc: "cheese.png", category: "dairy", baseShelfLifeDays: 10 },
            // Add more items with categories and base shelf lives
        ];

        // --- ASSET LOADING ---
        function preloadItemImages() {
            let assetsToLoad = possibleItems.length;
            if (assetsToLoad === 0) { assetsLoaded = true; return; }
            let loadedCount = 0;
            possibleItems.forEach(itemDef => {
                const img = new Image();
                img.onload = () => {
                    loadedCount++;
                    if (loadedCount === assetsToLoad) {
                        assetsLoaded = true;
                        console.log("All item assets loaded, Auditor.");
                    }
                };
                img.onerror = () => {
                    console.error(`Failed to load image: ${itemDef.imageSrc}`);
                    loadedCount++; // Still count it to not block game
                    if (loadedCount === assetsToLoad) assetsLoaded = true;
                };
                img.src = itemDef.imageSrc;
                itemImages[itemDef.id] = img;
            });
        }
        preloadItemImages();
        
        // --- DATE UTILITIES ---
        // Simple date formatting (expand or use a library for more robust needs)
        function formatDateForDisplay(date, formatStr) {
            if (!date || !(date instanceof Date)) return "??.??";
            const d = date.getDate();
            const m = date.getMonth() + 1;
            const y = date.getFullYear();
            const yy = y.toString().slice(-2);

            switch (formatStr) {
                case "DD/MM/YY": return `${String(d).padStart(2,'0')}/${String(m).padStart(2,'0')}/${yy}`;
                case "M/D/Y": return `${m}/${d}/${y}`;
                case "YYYY-MM-DD": return `${y}-${String(m).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
                case "D MMM YY": return `${d} ${date.toLocaleString('default', { month: 'short' })} ${yy}`;
                // Add more formats as needed by profiles
                default: return `${String(m).padStart(2,'0')}/${String(d).padStart(2,'0')}/${y}`; // Default to MM/DD/YYYY
            }
        }

        // Basic date parsing (VERY simplified, needs enhancement for real-world use)
        // For "Papers, Please" style, you'd want this to be a core mechanic of difficulty
        function parseDateString(dateStr, profileHint) {
            // This is a placeholder. A real implementation would try various formats,
            // especially those hinted by the profile.
            // For now, assume system always knows the actual date and label is for player.
            // In a more complex game, player would parse this, system would check their interpretation.
            if (!dateStr || dateStr.includes("?") || dateStr.toLowerCase().includes("vague")) return null; // Can't parse
            
            // Try some common formats, SUPER simplified
            const partsSlash = dateStr.split('/');
            const partsDash = dateStr.split('-');

            if (partsSlash.length === 3) { // e.g. MM/DD/YYYY or DD/MM/YYYY
                let m, d, y;
                // Extremely naive guess based on common US vs Euro
                if (profileHint && profileHint.preferredDateFormat && profileHint.preferredDateFormat.startsWith("DD/MM")) {
                    d = parseInt(partsSlash[0]); m = parseInt(partsSlash[1]); y = parseInt(partsSlash[2]);
                } else { // Default to MM/DD
                    m = parseInt(partsSlash[0]); d = parseInt(partsSlash[1]); y = parseInt(partsSlash[2]);
                }
                if (y < 100) y += 2000; // Assume 21st century for 2-digit years
                if (!isNaN(m) && !isNaN(d) && !isNaN(y)) return new Date(y, m - 1, d);
            }
            if (partsDash.length === 3) { // e.g. YYYY-MM-DD
                 let y = parseInt(partsDash[0]); let m = parseInt(partsDash[1]); let d = parseInt(partsDash[2]);
                 if (!isNaN(m) && !isNaN(d) && !isNaN(y)) return new Date(y, m - 1, d);
            }
            return null; // Failed to parse
        }

        function addDays(date, days) {
            const result = new Date(date);
            result.setDate(result.getDate() + days);
            return result;
        }

        function updateTodaysDateDisplay() {
            // today = new Date(); // Uncomment if 'today' should be current real date each game.
                                // For consistent "day" in game, set once at start.
            const options = { year: 'numeric', month: 'short', day: 'numeric' };
            todaysDateDisplay.textContent = `Audit Date: ${today.toLocaleDateString(undefined, options)}`;
        }


        // --- UI PANEL LOGIC ---
        function populateDirectory() {
            directoryContent.innerHTML = '';
            for (const userId in PEOPLE_PROFILES) {
                const profile = PEOPLE_PROFILES[userId];
                const profileDiv = document.createElement('div');
                profileDiv.classList.add('user-profile');
                let rulesHtml = '<ul>';
                profile.specialRules.forEach(rule => {
                    rulesHtml += `<li><strong>${rule.type.replace(/_/g, ' ')}:</strong> ${rule.note || rule.rule}</li>`;
                });
                rulesHtml += '</ul>';

                  let signatureInfo = '';
                if (profile.labelingHabits.signatureStyle) {
                    signatureInfo = `Signature: <strong>${profile.labelingHabits.signatureStyle}</strong> (used ~${profile.labelingHabits.signatureChance*100}% of time if name is accurate).`;
                }

                profileDiv.innerHTML = `
                    <h3>${profile.name} <span style="font-size:0.8em; color: #718096;">(${profile.title})</span></h3>
                    ${profile.portrait ? `<img src="${profile.portrait}" alt="${profile.name}" style="width:80px; height:auto; border-radius:5px; float:right; margin-left:10px;">` : ''}
                    <p><strong>Bio:</strong> ${profile.bio}</p>
                    <p><strong>Labeling:</strong> 
                        Preferred format: ${profile.labelingHabits.preferredDateFormat}. 
                        Name on label: ${profile.labelingHabits.nameAccuracy*100}% accurate. 
                        Date on label: ${profile.labelingHabits.dateAccuracy*100}% accurate.
                        Uses own format: ${profile.labelingHabits.usesPreferredFormat*100}% of time.
                        ${profile.labelingHabits.writesVagueDate ? `Writes vague dates: ${profile.labelingHabits.writesVagueDate*100}% chance.` : ''}
                    </p>
                    <p><strong>Specific Notes/Rules:</strong></p>
                    ${rulesHtml}
                `;
                directoryContent.appendChild(profileDiv);
            }
        }

        function populateManual() {
            manualContent.innerHTML = '';
            GENERAL_RULES.forEach(rule => {
                const ruleDiv = document.createElement('div');
                ruleDiv.classList.add('rule-section');
                ruleDiv.innerHTML = `
                    <h3>${rule.id}: ${rule.title}</h3>
                    <p>${rule.text}</p>
                `;
                manualContent.appendChild(ruleDiv);
            });
        }

        toggleDirectoryButton.addEventListener('click', () => userDirectoryPanel.classList.toggle('active'));
        toggleManualButton.addEventListener('click', () => manualPanel.classList.toggle('active'));
        
        document.querySelectorAll('.close-panel-button').forEach(button => {
            button.addEventListener('click', (e) => {
                document.getElementById(e.target.dataset.panelId).classList.remove('active');
            });
        });


        // --- CANVAS & ITEM DRAWING (Largely similar, but post-it content changes) ---
        function resizeCanvas() {
            const hostRect = gameHost.getBoundingClientRect();
            if (hostRect.width <= 0 || hostRect.height <= 0) { return false; }
            canvas.width = hostRect.width;
            canvas.height = hostRect.height;

            if (currentItem && !currentItem.isDragging) {
                // Recalculate item size and redraw its canvas representation
                let newImageDrawWidth = canvas.width * ITEM_SCALE_VS_CANVAS_WIDTH;
                let aspectRatio = (itemImages[currentItem.baseItem.id]?.naturalHeight || ITEM_ORIGINAL_HEIGHT) / 
                                  (itemImages[currentItem.baseItem.id]?.naturalWidth || ITEM_ORIGINAL_WIDTH);
                if (aspectRatio === 0) aspectRatio = 1;
                let newImageDrawHeight = newImageDrawWidth * aspectRatio;
                // ... (clamping logic from original game, simplified)
                const maxPixelDrawSize = Math.min(canvas.width * 0.5, canvas.height * 0.4);
                newImageDrawWidth = Math.min(newImageDrawWidth, maxPixelDrawSize);
                newImageDrawHeight = Math.min(newImageDrawHeight, maxPixelDrawSize * aspectRatio);


                const newItemDisplayCanvas = createItemCanvas(
                    itemImages[currentItem.baseItem.id],
                    currentItem.labelText.name, // Name ON THE LABEL
                    currentItem.labelText.date, // Date string ON THE LABEL
                    newImageDrawWidth, newImageDrawHeight,
                    // Pass stored random post-it properties for consistency
                    currentItem.postItProps.targetSize, currentItem.postItProps.widthFactor, currentItem.postItProps.heightFactor,
                    currentItem.postItProps.noteRotation, currentItem.postItProps.textRotation,
                    currentItem.postItProps.biasXFactor, currentItem.postItProps.biasYFactor,
                    currentItem.postItProps.jitterXFactor, currentItem.postItProps.jitterYFactor,
                    currentItem.postItProps.labelTextColor,
                    currentItem.visualSpoilage // NEW: Pass visual spoilage for potential visual effects
                );

                if (newItemDisplayCanvas && newItemDisplayCanvas.width > 0) {
                    currentItem.imageCanvas = newItemDisplayCanvas;
                    currentItem.width = newItemDisplayCanvas.width; 
                    currentItem.height = newItemDisplayCanvas.height;
                    currentItem.targetY = canvas.height * 0.28 - currentItem.height / 2; // Position higher
                    currentItem.x = canvas.width / 2 - currentItem.width / 2;
                    if (!currentItem.isFalling) currentItem.y = currentItem.targetY;
                }
            }
            return true;
        }

        function createItemCanvas(baseImage, labelName, labelDateStr,
                                targetItemDrawWidth, targetItemDrawHeight,
                                p_targetSize, p_widthFactor, p_heightFactor, 
                                p_noteRotation, p_textRotation,
                                p_biasXFactor, p_biasYFactor, 
                                p_jitterXFactor, p_jitterYFactor,
                                p_labelTextColor, visualSpoilage) {
            
            targetItemDrawWidth = Math.max(1, targetItemDrawWidth || 100);
            targetItemDrawHeight = Math.max(1, targetItemDrawHeight || 100);

            const itemCanvas = document.createElement('canvas');
            const itemCtx = itemCanvas.getContext('2d');
            
            const safe_p_targetSize = Math.max(20, p_targetSize);
            const labelWidth = Math.max(10, safe_p_targetSize * (1 + p_widthFactor)); 
            const labelHeight = Math.max(10, safe_p_targetSize * (1 + p_heightFactor)); 
            
            const maxOverhang = Math.max(labelWidth, labelHeight) * 0.6; 
            itemCanvas.width = targetItemDrawWidth + maxOverhang;
            itemCanvas.height = targetItemDrawHeight + maxOverhang;
            
            const itemImageX = (itemCanvas.width - targetItemDrawWidth) / 2;
            const itemImageY = (itemCanvas.height - targetItemDrawHeight) / 2;

            itemCtx.imageSmoothingEnabled = false; 
            if (baseImage && baseImage.complete && baseImage.naturalWidth !== 0) {
                 itemCtx.drawImage(baseImage, itemImageX, itemImageY, targetItemDrawWidth, targetItemDrawHeight);
                 // TODO: If visualSpoilage is true, draw a spoilage overlay (e.g., greenish tint, mold spots)
                 // Example:
                 if (visualSpoilage && currentItem && currentItem.baseItem.category !== 'drink' && !currentItem.baseItem.nonPerishable) { // Don't make drinks look moldy unless it's specific
                    itemCtx.globalAlpha = 0.2;
                    itemCtx.fillStyle = 'green';
                    itemCtx.fillRect(itemImageX, itemImageY, targetItemDrawWidth, targetItemDrawHeight);
                    itemCtx.globalAlpha = 1.0;
                 }

            } else { // Fallback drawing
                itemCtx.fillStyle = 'grey';
                itemCtx.fillRect(itemImageX, itemImageY, targetItemDrawWidth, targetItemDrawHeight);
                itemCtx.fillStyle = 'white'; itemCtx.textAlign = 'center'; itemCtx.textBaseline = 'middle';
                itemCtx.font = `${LABEL_FONT_SIZE_FALLBACK*1.2}px Arial`; 
                itemCtx.fillText("?", itemImageX + targetItemDrawWidth/2, itemImageY + targetItemDrawHeight/2);
            }
            itemCtx.imageSmoothingEnabled = true; 

            // Post-it note drawing (similar to original)
            itemCtx.save();
            const finalSideBiasX = targetItemDrawWidth * p_biasXFactor + labelWidth * p_biasXFactor * 0.2;
            const finalSideBiasY = targetItemDrawHeight * p_biasYFactor + labelHeight * p_biasYFactor * 0.2;
            const finalRandomJitterX = targetItemDrawWidth * p_jitterXFactor;
            const finalRandomJitterY = targetItemDrawHeight * p_jitterYFactor;
            const labelCenterX = itemImageX + targetItemDrawWidth / 2 + finalSideBiasX + finalRandomJitterX;
            const labelCenterY = itemImageY + targetItemDrawHeight / 2 + finalSideBiasY + finalRandomJitterY;
            
            itemCtx.translate(labelCenterX, labelCenterY); 
            itemCtx.rotate(p_noteRotation);
            itemCtx.globalAlpha = 0.93; itemCtx.fillStyle = '#FFFACD'; 
            itemCtx.shadowColor = 'rgba(0,0,0,0.25)'; itemCtx.shadowBlur = 8;
            itemCtx.shadowOffsetX = Math.cos(p_noteRotation + Math.PI/4) * 3;
            itemCtx.shadowOffsetY = Math.sin(p_noteRotation + Math.PI/4) * 3;
            itemCtx.fillRect(-labelWidth / 2, -labelHeight / 2, labelWidth, labelHeight); 
            itemCtx.shadowColor = 'transparent'; 
            // Speckles (optional)
            const speckleCount = 10; /* simplified */
            for (let i = 0; i < speckleCount; i++) { /* ... speckle drawing logic ... */ }
            
            itemCtx.globalAlpha = 1.0; itemCtx.strokeStyle = '#E0DCBE'; itemCtx.lineWidth = 1;
            itemCtx.strokeRect(-labelWidth / 2 + 0.5, -labelHeight / 2 + 0.5, labelWidth - 1, labelHeight - 1);

            itemCtx.fillStyle = p_labelTextColor;
            const calculatedFontSize = labelHeight * POST_IT_FONT_SCALE_VS_LABEL_HEIGHT;
            const postItFontSize = Math.max(MIN_POST_IT_FONT_SIZE, Math.min(MAX_POST_IT_FONT_SIZE, calculatedFontSize));
            itemCtx.font = `${postItFontSize}px Schoolbell, cursive`;
            itemCtx.textAlign = 'center'; itemCtx.textBaseline = 'middle';
            itemCtx.save();
            itemCtx.rotate(p_textRotation);
            
            const lineOffset = postItFontSize * 1.2; 
            itemCtx.fillText(labelName || "Name: ???", 0, -lineOffset / 2);
            itemCtx.fillText(labelDateStr || "Date: ???", 0, lineOffset / 2);
            itemCtx.restore(); // text rotation
            itemCtx.restore(); // post-it transformations
            return itemCanvas;
        }


        // --- NEW ITEM GENERATION & DECISION LOGIC ---
        function spawnNewItem() {
            if (!assetsLoaded || gameHost.style.display === 'none') {
                setTimeout(spawnNewItem, 100); return;
            }
            if (possibleItems.length === 0) { 
                console.error("No base items defined in possibleItems."); return; 
            }
             if (canvas.width === 0 || canvas.height === 0) { // Wait for canvas to be sized
                if(!resizeCanvas()) { setTimeout(spawnNewItem, 100); return; }
            }

            const baseItemDef = possibleItems[Math.floor(Math.random() * possibleItems.length)];
            const ownerProfileKeys = Object.keys(PEOPLE_PROFILES);
            const ownerId = ownerProfileKeys[Math.floor(Math.random() * ownerProfileKeys.length)];
            const actualOwnerProfile = PEOPLE_PROFILES[ownerId];

            // 1. Determine ACTUAL item properties
            let actualExpirationDate;
            const daysFromToday = Math.floor(Math.random() * (baseItemDef.baseShelfLifeDays + 10)) - 5; // -5 to +shelfLife+5 days
            actualExpirationDate = addDays(today, daysFromToday);
            
            let isActuallyExpired = actualExpirationDate < today;
            let visualSpoilage = isActuallyExpired && Math.random() < 0.7; // 70% chance expired items look spoiled (if perishable)
            if (baseItemDef.nonPerishable) visualSpoilage = false;


            // 2. Generate LABELED properties (may be intentionally incorrect)
            // --- NAME LOGIC ---
            let labeledOwnerName; 
            const ownerAttributionRoll = Math.random();
            let profileForLabeling = actualOwnerProfile; 

            if (ownerAttributionRoll > actualOwnerProfile.labelingHabits.nameAccuracy) {
                const otherOwnerKeys = ownerProfileKeys.filter(k => k !== ownerId);
                if (otherOwnerKeys.length > 0 && Math.random() < 0.7) {
                    profileForLabeling = PEOPLE_PROFILES[otherOwnerKeys[Math.floor(Math.random() * otherOwnerKeys.length)]];
                    if (profileForLabeling.labelingHabits.signatureStyle && Math.random() < profileForLabeling.labelingHabits.signatureChance) {
                        labeledOwnerName = profileForLabeling.labelingHabits.signatureStyle;
                    } else {
                        labeledOwnerName = profileForLabeling.name; 
                    }
                } else {
                    labeledOwnerName = ["My Stuff", "Food", "Handle With Care", ""][Math.floor(Math.random() * 4)];
                }
            } else {
                if (actualOwnerProfile.labelingHabits.signatureStyle && Math.random() < actualOwnerProfile.labelingHabits.signatureChance) {
                    labeledOwnerName = actualOwnerProfile.labelingHabits.signatureStyle;
                } else {
                    labeledOwnerName = actualOwnerProfile.name; 
                }
            }
            if (labeledOwnerName === "") labeledOwnerName = " "; 

            // --- DATE LOGIC (Moved up and corrected) ---
            let labeledDateString; // Declare here
            let dateForLabel = new Date(actualExpirationDate); 
            if (Math.random() > actualOwnerProfile.labelingHabits.dateAccuracy) {
                const offsetError = Math.floor(Math.random() * 10) - 5; 
                dateForLabel = addDays(actualExpirationDate, offsetError);
            }

            // Use the profileForLabeling's habits for date format if name was misattributed, 
            // otherwise use actualOwnerProfile's habits.
            const profileToUseForDateFormat = (profileForLabeling !== actualOwnerProfile && ownerAttributionRoll > actualOwnerProfile.labelingHabits.nameAccuracy) 
                                            ? profileForLabeling 
                                            : actualOwnerProfile;

            if (Math.random() > profileToUseForDateFormat.labelingHabits.usesPreferredFormat && profileToUseForDateFormat.labelingHabits.alternativeDateFormats && profileToUseForDateFormat.labelingHabits.alternativeDateFormats.length > 0) {
                const altFormats = profileToUseForDateFormat.labelingHabits.alternativeDateFormats;
                labeledDateString = formatDateForDisplay(dateForLabel, altFormats[Math.floor(Math.random()*altFormats.length)]);
            } else {
                labeledDateString = formatDateForDisplay(dateForLabel, profileToUseForDateFormat.labelingHabits.preferredDateFormat);
            }

            if (profileToUseForDateFormat.labelingHabits.writesVagueDate && Math.random() < profileToUseForDateFormat.labelingHabits.writesVagueDate) {
                labeledDateString = ["Tomorrow-ish", "Good for a bit", "Mon", "Next week?", "Old"][Math.floor(Math.random()*5)];
            }
            if (Math.random() < 0.1) { 
                labeledDateString = "";
            }
            if (labeledDateString === "") labeledDateString = " ";


            // 3. Determine CORRECT ACTION based on ALL info (actuals, labels, rules)
            const itemDataForDecision = {
                baseItem: baseItemDef,
                actualOwnerProfile: actualOwnerProfile,
                labeledOwnerName: labeledOwnerName, // This is now defined
                actualExpirationDate: actualExpirationDate,
                isActuallyExpired: isActuallyExpired,
                visualSpoilage: visualSpoilage,
                labeledDateString: labeledDateString, // THIS IS NOW DEFINED BEFORE USE
            };
            const correctDecision = determineCorrectDecision(itemDataForDecision);

            // 4. Setup for drawing
            let currentItemDrawWidth = canvas.width * ITEM_SCALE_VS_CANVAS_WIDTH;
            let aspectRatio = (itemImages[baseItemDef.id]?.naturalHeight || ITEM_ORIGINAL_HEIGHT) / 
                              (itemImages[baseItemDef.id]?.naturalWidth || ITEM_ORIGINAL_WIDTH);
            if (aspectRatio === 0) aspectRatio = 1;
            let currentItemDrawHeight = currentItemDrawWidth * aspectRatio;
            const maxPixelDrawSize = Math.min(canvas.width * 0.5, canvas.height * 0.4);
            currentItemDrawWidth = Math.min(currentItemDrawWidth, maxPixelDrawSize);
            currentItemDrawHeight = Math.min(currentItemDrawHeight, maxPixelDrawSize * aspectRatio);


            const targetPostItSize = Math.max(20, Math.min(currentItemDrawWidth, currentItemDrawHeight) * POST_IT_SCALE_VS_ITEM_DIMENSION);
            const postItRandomProps = { // Store these for consistent redraw on resize
                targetSize: targetPostItSize,
                widthFactor: (Math.random() - 0.5) * 0.2, heightFactor: (Math.random() - 0.5) * 0.2,
                noteRotation: (Math.random() - 0.5) * 0.25, textRotation: (Math.random() - 1) * 0.05,
                biasXFactor: (Math.random() < 0.5 ? -1:1) * 0.20, biasYFactor: (Math.random() < 0.5 ? -1:1) * 0.20,
                jitterXFactor: (Math.random() - 0.5) * 0.05, jitterYFactor: (Math.random() - 0.5) * 0.05,
                labelTextColor: labelTextColors[Math.floor(Math.random() * labelTextColors.length)]
            };
            
            const itemDisplayCanvas = createItemCanvas(
                itemImages[baseItemDef.id], 
                labeledOwnerName, // Use the generated name/signature
                labeledDateString, // Use the generated date string
                currentItemDrawWidth, currentItemDrawHeight,
                postItRandomProps.targetSize, postItRandomProps.widthFactor, postItRandomProps.heightFactor,
                postItRandomProps.noteRotation, postItRandomProps.textRotation,
                postItRandomProps.biasXFactor, postItRandomProps.biasYFactor,
                postItRandomProps.jitterXFactor, postItRandomProps.jitterYFactor,
                postItRandomProps.labelTextColor,
                visualSpoilage
            );
            
            if (!itemDisplayCanvas || itemDisplayCanvas.width === 0) {
                console.error("SPAWN: createItemCanvas failed. Retrying.");
                setTimeout(spawnNewItem, 100); return;
            }

            currentItem = {
                imageCanvas: itemDisplayCanvas,
                baseItem: baseItemDef,
                actualOwnerProfile: actualOwnerProfile, 
                actualExpirationDate: actualExpirationDate,
                isActuallyExpired: isActuallyExpired,
                visualSpoilage: visualSpoilage,
                labelText: { name: labeledOwnerName, date: labeledDateString }, 
                postItProps: postItRandomProps, 
                correctDecision: correctDecision,

                width: itemDisplayCanvas.width, height: itemDisplayCanvas.height,
                x: canvas.width / 2 - itemDisplayCanvas.width / 2,
                y: -(itemDisplayCanvas.height), 
                targetY: canvas.height * 0.28 - itemDisplayCanvas.height / 2, 
                isDragging: false, isFalling: true, dragOffsetX: 0, dragOffsetY: 0,
                dragRotation: 0, lastMouseX: 0,
            };
            playSfx(popSound);
            messageArea.textContent = `New item for audit.`; // Removed specific details to avoid giving away too much
            setCanvasCursor();
        }

        function determineCorrectDecision(itemData) {
            const { baseItem, actualOwnerProfile, isActuallyExpired, visualSpoilage, labeledOwnerName, labeledDateString } = itemData;
            let decisionReason = ""; // For detailed feedback

            // Rule 1: CEO Exception (Kenzo)
            if (actualOwnerProfile.id === 'kenzo') {
                const ceoRule = actualOwnerProfile.specialRules.find(r => r.type === 'priority_owner');
                if (isActuallyExpired && visualSpoilage) {
                     decisionReason = "CEO's item, but it's visibly spoiled AND past its real expiry. Discard.";
                    return { action: "Discard", reason: decisionReason };
                }
                 decisionReason = "CEO's item. Policy: Keep unless undeniably hazardous.";
                return { action: "Keep", reason: decisionReason };
            }

            // Rule 2: Visual Spoilage (General Rule GR005)
            if (visualSpoilage && baseItem.category !== 'cheese' && !baseItem.nonPerishable) { // Moldy cheese can be okay
                // Check if any owner rule overrides this for this item type (unlikely)
                 decisionReason = "Item shows clear visual spoilage. Policy: Discard.";
                return { action: "Discard", reason: decisionReason };
            }

            // Rule 3: Actual Expiration (General Rule GR003)
            if (isActuallyExpired) {
                // Check if any owner rule allows keeping this specific expired item
                // e.g., Fran's wine rule
                if (baseItem.id === 'wine' && actualOwnerProfile.id === 'fran') {
                    const wineRule = actualOwnerProfile.specialRules.find(r => r.item_id === 'wine');
                    // Simplified: If it's Fran's wine and not TOO old. A real rule would parse dates.
                    const ageInDays = (today - itemData.actualExpirationDate) / (1000 * 60 * 60 * 24);
                    if (ageInDays < 30) { // Example: wine can be a bit past 'best by'
                         decisionReason = "Fran's wine; slightly past date but per her rule, okay for a bit if not visually bad.";
                        return { action: "Keep", reason: decisionReason };
                    }
                }
                 decisionReason = `Item is past its actual expiration date (${itemData.actualExpirationDate.toLocaleDateString()}). Policy: Discard.`;
                return { action: "Discard", reason: decisionReason };
            }

            // Rule 4: Labeling Policy Violations (GR001, GR002) - if item is NOT actually expired or spoiled
            // This part becomes very complex. For now, if it's not expired/spoiled, we keep it.
            // A full implementation would check:
            // - Is labeledOwnerName missing or "non-compliant"?
            // - Is labeledDateString missing, unparseable, or indicates expired (even if actual is fine)?
            // For example: If labeledOwnerName is empty and item is perishable => Discard (GR002)
            if (!labeledOwnerName || labeledOwnerName.trim() === "" || labeledOwnerName === "Name: ???") {
                 if (!baseItem.nonPerishable) {
                    decisionReason = "Perishable item with missing owner name on label. Policy: Discard (after hold).";
                    return { action: "Discard", reason: decisionReason };
                 }
            }
            if (!labeledDateString || labeledDateString.trim() === "" || labeledDateString === "Date: ???") {
                 if (!baseItem.nonPerishable) {
                    decisionReason = "Perishable item with missing date on label. Policy: Discard (after hold).";
                    return { action: "Discard", reason: decisionReason };
                 }
            }
            
            // Default: If no discard rule triggered, keep it.
            decisionReason = "Item appears compliant with current checks (not expired, not visibly spoiled, basic label info present).";
            return { action: "Keep", reason: decisionReason };
        }


        // --- ITEM INTERACTION & GAME LOOP (largely similar, scoring/combo removed) ---
        function drawItem() { /* Same as original */ 
            if (!currentItem) return;
            ctx.save();
            const itemCenterX = currentItem.x + currentItem.width / 2;
            const itemCenterY = currentItem.y + currentItem.height / 2;
            ctx.translate(itemCenterX, itemCenterY);
            ctx.rotate(currentItem.dragRotation);
            ctx.drawImage( currentItem.imageCanvas, -currentItem.width / 2, -currentItem.height / 2, currentItem.width, currentItem.height );
            ctx.restore();
        }

        function updateItemPosition() { /* Same as original */
             if (!currentItem || currentItem.isDragging) return;
            if (currentItem.isFalling) {
                const dy = currentItem.targetY - currentItem.y;
                if (Math.abs(dy) < 1) {
                    currentItem.y = currentItem.targetY; currentItem.isFalling = false;
                    canvas.classList.remove('item-pop'); void canvas.offsetWidth; canvas.classList.add('item-pop');
                    if (!currentItem.isDragging) canvas.style.cursor = 'grab';
                } else {
                    currentItem.y += dy * 0.08; // Slower fall
                }
            }
        }
        
        function checkItemOverlapWithBins() { /* Same as original */
            if (!currentItem) return null;
            const canvasRect = canvas.getBoundingClientRect();
            const itemRectVP = {
                left: canvasRect.left + currentItem.x, right: canvasRect.left + currentItem.x + currentItem.width,
                top: canvasRect.top + currentItem.y, bottom: canvasRect.top + currentItem.y + currentItem.height,
                centerX: canvasRect.left + currentItem.x + currentItem.width / 2,
            };
            let overlappedBin = null;
            for (const binType in binsElements) {
                const binEl = binsElements[binType];
                const binRectVP = binEl.getBoundingClientRect();
                const itemEffectiveBottom = itemRectVP.bottom - (currentItem.height * 0.2);
                if (itemRectVP.left < binRectVP.right && itemRectVP.right > binRectVP.left &&
                    itemRectVP.top < binRectVP.bottom && itemEffectiveBottom > binRectVP.top) {
                    if (itemRectVP.centerX > binRectVP.left && itemRectVP.centerX < binRectVP.right) {
                        overlappedBin = binType; break;
                    }
                    if (!overlappedBin) overlappedBin = binType;
                }
            }
            return overlappedBin;
        }
        
        function setCanvasCursor() { /* Same as original */
            if (!currentItem || gameHost.style.display === 'none') { canvas.style.cursor = 'default'; return; }
            if (currentItem.isDragging) canvas.style.cursor = 'grabbing';
            else if (!currentItem.isFalling) canvas.style.cursor = 'grab';
            else canvas.style.cursor = 'default';
        }

        canvas.addEventListener('mousedown', (e) => { /* Largely same */
            if (!currentItem || e.button !== 0) return; // Can grab if falling
            const mouseX = e.offsetX; const mouseY = e.offsetY;
            if (mouseX >= currentItem.x && mouseX <= currentItem.x + currentItem.width &&
                mouseY >= currentItem.y && mouseY <= currentItem.y + currentItem.height) {
                currentItem.isFalling = false; currentItem.isDragging = true;
                currentItem.dragOffsetX = mouseX - currentItem.x;
                currentItem.dragOffsetY = mouseY - currentItem.y;
                setCanvasCursor(); 
            }
        });
        
        document.addEventListener('mousemove', (e) => { /* Largely same */
            if (!currentItem || !currentItem.isDragging) {
                if (currentlyOverlappedBinType) {
                    binsElements[currentlyOverlappedBinType].classList.remove('drag-over');
                    currentlyOverlappedBinType = null;
                }
                return;
            }
            e.preventDefault(); 
            const rect = canvas.getBoundingClientRect();
            const mouseXInCanvas = e.clientX - rect.left;
            const mouseYInCanvas = e.clientY - rect.top;

            if (currentItem.lastMouseX !== 0) { /* Drag rotation logic from original */
                const dx = mouseXInCanvas - currentItem.lastMouseX;
                const tiltFactor = 0.01; let targetRotation = dx * tiltFactor;
                const maxTilt = 0.35; 
                targetRotation = Math.max(-maxTilt, Math.min(maxTilt, targetRotation));
                currentItem.dragRotation += (targetRotation - currentItem.dragRotation) * 0.2; 
            }
            currentItem.lastMouseX = mouseXInCanvas;
            currentItem.x = mouseXInCanvas - currentItem.dragOffsetX;
            currentItem.y = mouseYInCanvas - currentItem.dragOffsetY;

            const binTypeActuallyOverlapped = checkItemOverlapWithBins();
            if (binTypeActuallyOverlapped) {
                if (currentlyOverlappedBinType && currentlyOverlappedBinType !== binTypeActuallyOverlapped) {
                    binsElements[currentlyOverlappedBinType].classList.remove('drag-over');
                }
                if (binsElements[binTypeActuallyOverlapped]) { 
                    binsElements[binTypeActuallyOverlapped].classList.add('drag-over');
                    currentlyOverlappedBinType = binTypeActuallyOverlapped;
                }
            } else {
                if (currentlyOverlappedBinType) {
                    binsElements[currentlyOverlappedBinType].classList.remove('drag-over');
                    currentlyOverlappedBinType = null;
                }
            }
        });

        document.addEventListener('mouseup', (e) => {
            if (!currentItem || !currentItem.isDragging || e.button !== 0) return;
            
            const droppedOnBinType = checkItemOverlapWithBins(); 

            if (currentlyOverlappedBinType) {
                binsElements[currentlyOverlappedBinType].classList.remove('drag-over');
                currentlyOverlappedBinType = null;
            }
            currentItem.isDragging = false;
            
            if (droppedOnBinType) { // droppedOnBinType is "Keep" or "Discard"
                const decisionResult = currentItem.correctDecision; // This is { action: "Keep/Discard", reason: "..." }
                if (droppedOnBinType === decisionResult.action) {
                    messageArea.innerHTML = `<strong style="color:green;">Correct!</strong> ${decisionResult.reason}`;
                    playSfx(correctSortSound);
                    // Potentially track correct decisions for end-of-day summary
                } else {
                    messageArea.innerHTML = `<strong style="color:red;">Incorrect.</strong> Expected ${decisionResult.action}. ${decisionResult.reason} <br/>Auditor's choice: ${droppedOnBinType}.`;
                    playSfx(incorrectSortSound);
                    // Potentially track incorrect decisions
                }
                currentItem = null; 
                itemsProcessedThisDay++;
                if (itemsProcessedThisDay >= ITEMS_PER_DAY) {
                    endOfDay();
                } else {
                    setTimeout(spawnNewItem, 3000); // Longer delay for reading feedback
                }
            } else { 
                // Returned to middle
                currentItem.x = canvas.width / 2 - currentItem.width / 2;
                currentItem.y = currentItem.targetY;
                currentItem.isFalling = false;
            }
            setCanvasCursor(); 
        });
        
        function playSfx(sound) {
            sound.currentTime = 0;
            sound.play().catch(e => console.warn("SFX play failed", e));
        }

        function gameLoop() {
           ctx.clearRect(0, 0, canvas.width, canvas.height);
           if (assetsLoaded && currentItem) {
               updateItemPosition();
               drawItem();
           }
           requestAnimationFrame(gameLoop);
        }

        function endOfDay() {
            messageArea.textContent = `End of Audit Day ${gameDay}. Items processed: ${itemsProcessedThisDay}. Prepare for next day.`;
            // Here you could show a summary screen, save progress, etc.
            // For now, just reset for a "new day" if player continues (not implemented yet)
            // Or just stop.
            console.log("End of Day reached.");
            // To restart automatically for testing:
            // gameDay++;
            // itemsProcessedThisDay = 0;
            // currentDayDisplay.textContent = `Audit Day: ${gameDay}`;
            // setTimeout(spawnNewItem, 2000);
            // For now, the game will just stop spawning new items.
             if(currentItem) currentItem = null; // Clear current item
             ctx.clearRect(0, 0, canvas.width, canvas.height); // Clear canvas
        }
        
        // --- INITIALIZATION ---
        startButton.addEventListener('click', () => {
           introScreen.style.display = 'none';
           gameHost.style.display = 'flex'; 
           today = new Date(); // Set "today" for this game session/day
           
           function attemptStart() {
               if (resizeCanvas()) { 
                   updateTodaysDateDisplay();
                   populateDirectory();
                   populateManual();

                   bgMusic.loop = true; bgMusic.volume = 0.1;
                   bgMusic.play().catch(e => console.warn("BG music play failed", e));
                   
                   const checkAssetsAndStartLoop = () => {
                       if (assetsLoaded) {
                           currentDayDisplay.textContent = `Audit Day: ${gameDay}`;
                           itemsProcessedThisDay = 0;
                           spawnNewItem(); 
                           gameLoop();
                       } else {
                           setTimeout(checkAssetsAndStartLoop, 100); 
                       }
                   };
                   checkAssetsAndStartLoop();
               } else {
                   requestAnimationFrame(attemptStart); 
               }
           }
           requestAnimationFrame(attemptStart); 
        });

        window.addEventListener('resize', () => {
            resizeCanvas();
            if (currentItem && !currentItem.isDragging) {
                currentItem.x = canvas.width / 2 - currentItem.width / 2;
                if (!currentItem.isFalling) currentItem.y = currentItem.targetY;
            }
            setCanvasCursor();
        });

        if (!milkImage.src) { // Basic asset check
             messageArea.textContent = "Warning: Core image assets might be missing. Check paths.";
        }
    </script>
</body>
</html>