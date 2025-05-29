<?php

require_once('/var/www/html/blocks/session.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cold Storage Queen's Fridge Frenzy! DIVA EDITION 💅</title>
    <style>
        body {
            font-family: 'Comic Sans MS', 'Chalkboard SE', 'Marker Felt', sans-serif;
            background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 50%, #ff9a9e 100%);
            color: #5D4037;
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
            /* MODIFICATION: Increase width percentage and max-width */
            width: 95vw;       /* Increased from 90vw to give more horizontal space */
            height: 95vh;      /* Height can probably stay the same or adjust if needed */
            max-width: 1200px; /* Increased from 1000px to allow it to get wider on large screens */
            max-height: 800px; /* Can stay the same or adjust */
            background-color: #E0F7FA;
            border-radius: 25px;
            box-shadow: 0 15px 30px rgba(0,0,0,0.25), inset 0 0 20px rgba(0,0,0,0.15);
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
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
            cursor: grab; /* Default cursor for canvas */
        }

        #gameUiContainer {
            width: 100%;
            padding: 10px 20px 20px 20px; /* More bottom padding for bins */
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 15px; /* Increased gap */
            position: relative;
            z-index: 1;
        }

        #binsContainer {
            display: flex;
            /* justify-content: space-between; */ /* Switch to space-around for more even spacing with wider bins */
            justify-content: space-around; 
            align-items: flex-end; 
            width: 100%; /* Or slightly less if you want padding on gameHost, e.g., 95% */
            gap: 10px; /* Reduce gap slightly if bins are wider */
            flex-shrink: 0;
            margin-top: 10px; 
        }

        .bin {
            flex-grow: 1; 
            /* min-width: 120px; */ /* Remove min-width or make it larger if needed */
            min-width: 250px; /* Example: larger min-width */
            /* max-width: 23%; */ /* Change this to allow wider bins */
            max-width: 24%;  /* Will take up almost a quarter each, with gap */
            padding: 15px 10px; 
            border-radius: 15px 15px 0 0; 
            font-weight: bold;
            font-size: clamp(0.9em, 1.6vw, 1.2em); 
            cursor: default;
            transition: transform 0.2s ease-out, box-shadow 0.2s ease-out, background-color 0.2s;
            box-shadow: 0 5px 8px rgba(0,0,0,0.1), inset 0 2px 4px rgba(0,0,0,0.05);
            text-align: center;
            background-clip: padding-box;
            border-top: 5px solid transparent; 
            min-height: 75px; /* Slightly taller perhaps */
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .bin:hover {
            transform: translateY(-8px) scale(1.02); /* More pronounced hover */
            box-shadow: 0 10px 15px rgba(0,0,0,0.2), inset 0 3px 6px rgba(0,0,0,0.08);
        }
        /* Unique top border colors on hover for better distinction */
        #bin-name-missing { background-color: #FFEBEE; color: #B71C1C; border-bottom: 5px solid #FFCDD2; }
        #bin-name-missing:hover { border-top-color: #E57373; background-color: #FFCDD2;}

        #bin-date-missing { background-color: #E8EAF6; color: #1A237E; border-bottom: 5px solid #C5CAE9; }
        #bin-date-missing:hover { border-top-color: #7986CB; background-color: #C5CAE9;}

        #bin-correctly-labeled { background-color: #E8F5E9; color: #1B5E20; border-bottom: 5px solid #A5D6A7; } /* Light Green */
        #bin-correctly-labeled:hover { border-top-color: #66BB6A; background-color: #C8E6C9;}

        #bin-expired { background-color: #E0F2F1; color: #004D40; border-bottom: 5px solid #B2DFDB; }
        #bin-expired:hover { border-top-color: #4DB6AC; background-color: #B2DFDB;}

    .bin.drag-over { /* New style for when an item is dragged over a bin */
            transform: translateY(-8px) scale(1.05) !important; /* Ensure it overrides normal hover slightly */
            box-shadow: 0 0 20px rgba(255, 223, 186, 0.9), /* A soft glow, like peach */
                        0 12px 20px rgba(0,0,0,0.25), 
                        inset 0 3px 6px rgba(0,0,0,0.1);
            /* You can change background color too if you want a more drastic effect */
            /* background-color: #FFDAB9 !important; /* Example: Peach Puff */
        }
        /* Make sure the top border color is still prominent */
        #bin-name-missing.drag-over { border-top-color: #D32F2F !important; }
        #bin-date-missing.drag-over { border-top-color: #303F9F !important; }
        #bin-all-missing.drag-over { border-top-color: #FBC02D !important; }
        #bin-expired.drag-over { border-top-color: #00796B !important; }

        #scoreBoard {
            font-size: clamp(1.6em, 3.2vw, 2.4em); /* Slightly larger */
            color: #D81B60;
            font-weight: bold;
            text-shadow: 2px 2px 3px #fff;
            flex-shrink: 0;
        }

        #messageArea {
            padding: 12px 18px; /* More padding */
            background-color: rgba(255, 255, 255, 0.92);
            border-radius: 15px; /* More rounded */
            font-size: clamp(1em, 1.9vw, 1.3em); /* Slightly larger */
            color: #AD1457;
            max-width: 90%;
            width: 550px; /* Slightly wider */
            box-shadow: 0 5px 10px rgba(0,0,0,0.12);
            line-height: 1.5;
            flex-shrink: 0;
            min-height: 60px; /* More min height */
        }
              #introScreen {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(255, 154, 158, 0.97) 0%, rgba(254, 207, 239, 0.97) 50%, rgba(255, 154, 158, 0.97) 100%);
            z-index: 1000;
            display: flex;
            flex-direction: column; /* Stack content vertically */
            align-items: center;    /* Center horizontally */
            justify-content: center;/* Center vertically */
            padding: 20px;
            box-sizing: border-box;
            text-align: center;
            overflow: hidden; /* Prevent any accidental scrollbars */
        }

        /* Wrapper for all content to be centered */
        .intro-content-centered {
            background-color: rgba(255, 255, 255, 0.98);
            padding: 30px; /* Uniform padding */
            border-radius: 30px;
            box-shadow: 0 15px 40px rgba(80, 40, 40, 0.3);
            max-width: 850px; /* Adjust max-width to fit brief text + image comfortably */
            width: 90%;
            color: #4A3B34;
            display: flex;
            flex-direction: column; /* Stack image, text, button */
            align-items: center;    /* Center items within this box */
            gap: 15px; /* Space between elements */
        }

        #introImage {
            max-width: 320px; /* Adjust for your image, slightly larger allowed now */
            width: 60%; /* Relative width for responsiveness */
            max-height: 300px; /* Max height for image */
            height: auto;
            border-radius: 15px;
            /* margin-bottom: 20px; /* Replaced by gap */
        }
        
        .intro-text-block {
            /* No overflow or max-height needed if text is brief */
        }

        .intro-text-block h1 {
            color: #E91E63; 
            font-size: clamp(1.8em, 4.5vw, 2.5em); /* Adjusted for impact */
            margin-bottom: 5px;
            margin-top: 0; /* Remove top margin if any */
        }
        
        .intro-text-block h2 {
            color: #AD1457; 
            font-size: clamp(1.3em, 3vw, 1.7em);
            margin-top: 0;
            margin-bottom: 15px;
        }

        .intro-text-block p {
            font-size: clamp(0.9em, 2vw, 1.1em); /* Slightly smaller for brevity */
            line-height: 1.55;
            margin-bottom: 10px; /* Less margin for tighter text */
            color: #5D4037;
        }

        .intro-text-block p.emphasis-text { /* For the TRASHED. YEETED. GONE. line */
            font-size: clamp(1em, 2.5vw, 1.3em);
            font-weight: bold;
            color: #D81B60; /* Standout pink */
            margin: 15px 0; /* More vertical space around it */
        }

        .intro-text-block p strong {
            color: #C2185B;
            font-weight: bold;
        }

        #startButton {
            margin-top: 15px; /* Add some space above the button */
            /* Other button styles remain mostly the same from your previous version */
            background-color: #FF69B4; /* Start with the hot pink! */
            color: white; 
            padding: 15px 35px; 
            border: none; 
            border-radius: 12px; 
            font-size: clamp(1.1em, 2.3vw, 1.4em);
            cursor: pointer; 
            transition: background-color 0.3s ease, transform 0.2s ease;
            font-family: 'Comic Sans MS', 'Chalkboard SE', 'Marker Felt', sans-serif;
            box-shadow: 0 5px 8px rgba(0,0,0,0.15);
        }
        #startButton:hover { 
            background-color: #E91E63; /* Darker pink on hover */
            transform: scale(1.05) translateY(-2px);
            box-shadow: 0 8px 12px rgba(0,0,0,0.2);
        }


              #comboDisplay {
            font-size: clamp(1.4em, 2.8vw, 2em); /* Responsive font size */
            color: #FF69B4; /* Hot pink */
            font-weight: bold;
            margin-top: 8px;
            min-height: 1.2em; /* Ensure space even when empty */
            text-shadow: 1px 1px 2px rgba(255,255,255,0.7);
            transition: transform 0.2s ease-out, opacity 0.2s ease-out; /* For smooth appearance */
            opacity: 0; /* Start hidden */
            transform: scale(0.8); /* Start small */
        }

        #comboDisplay.active {
            opacity: 1;
            transform: scale(1);
        }

        #comboDisplay.pop {
            animation: popEffect 0.3s ease-out;
        }

        @keyframes popEffect {
            0% { transform: scale(1); }
            50% { transform: scale(1.3); }
            100% { transform: scale(1); }
        }


#gameTopHud {
    position: absolute;
    top: 15px;
    left: 15px;
    right: 15px;
    display: flex;
    /* justify-content: space-between; /* We will control spacing with widths */
    align-items: flex-start;
    z-index: 20;
    pointer-events: none;
    font-family: inherit;
}

#gameTimerDisplay {
    font-size: clamp(1em, 2.5vw, 1.4em);
    color: #C2185B;
    font-weight: bold;
    width: 25%; /* Example: Adjust as needed */
    text-align: left; /* Ensure timer text is left-aligned in its container */
}

#scoreBoard {
    font-size: clamp(1.2em, 3vw, 1.6em);
    color: #D81B60;
    font-weight: bold;
    text-shadow: 1px 1px 2px #fff;
    width: 50%; /* Example: Give score more space */
    text-align: center; /* Center score text in its container */
}

#comboContainer {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    text-align: right;
    width: 25%; /* Example: Adjust as needed */
    /* min-height helps reserve space even if combo is not visible, but might not be perfect */
    /* min-height: 40px; /* Adjust this based on combo text + bar height */
}

#comboDisplay {
    /* Inherits styles from the previous #comboDisplay rule if ID is the same */
    /* font-size, color, font-weight, text-shadow etc. are already defined */
    margin-top: 0; 
    margin-bottom: 5px; /* Space between combo text and timer bar */
    min-height: 1em; /* Ensure space even when empty */
}

#comboTimerBarContainer { /* This is the "track" for the combo timer */
    width: 100px; 
    height: 8px;  
    background-color: rgba(93, 64, 55, 0.2); /* A darker, semi-transparent track color */
    border-radius: 4px; 
    overflow: hidden; /* Crucial to clip the inner bar */
    box-shadow: inset 0 1px 2px rgba(0,0,0,0.15); 
    margin: 0; 
    display: none; /* Start hidden - JS will make it 'block' when active */
}

#comboTimerBar { /* This is the actual colored progressing bar */
    width: 100%; /* Starts full, JS will decrease it */
    height: 100%;
    background-color: #FF69B4; /* Hot pink fill color */
    border-radius: 4px; /* Match container or slightly less */
    transition: width 0.1s linear; /* Smooths the width change */
}

.bin.expand {
    transform: translateY(-12px) scale(1.1) !important;
    box-shadow: 0 12px 25px rgba(0,0,0,0.25), inset 0 3px 6px rgba(0,0,0,0.1);
    border-width: 6px !important;
    z-index: 5; /* bring above others */
}

@keyframes itemPop {
  0% {
    transform: scale(1) rotate(0deg);
  }
  30% {
    transform: scale(1.15) rotate(1deg);
  }
  60% {
    transform: scale(0.98) rotate(-1deg);
  }
  100% {
    transform: scale(1) rotate(0deg);
  }
}

.item-pop {
  animation: itemPop 0.4s ease-out;
}

 #startButtonsContainer button {
            background-color: #FF69B4; /* Hot pink! */
            color: white; 
            padding: 15px 25px; /* Adjusted padding slightly for potentially longer text */
            border: none; 
            border-radius: 12px; 
            font-size: clamp(1.0em, 2.1vw, 1.3em); /* Slightly adjusted font size */
            cursor: pointer; 
            transition: background-color 0.3s ease, transform 0.2s ease;
            font-family: 'Comic Sans MS', 'Chalkboard SE', 'Marker Felt', sans-serif;
            box-shadow: 0 5px 8px rgba(0,0,0,0.15);
            text-align: center;
        }
        #startButtonsContainer button:hover { 
            background-color: #E91E63; /* Darker pink on hover */
            transform: scale(1.05) translateY(-2px);
            box-shadow: 0 8px 12px rgba(0,0,0,0.2);
        }

        /* Specific style for the Endless button if you want a different color */
        #startEndlessButton {
            background-color: #9C27B0; /* A fabulous purple */
        }
        #startEndlessButton:hover {
            background-color: #7B1FA2; /* Darker purple */
        }
    </style>

        <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Schoolbell&display=swap" rel="stylesheet">
</head>
<body>

    <div id="introScreen">
        <div class="intro-content-centered"> <!-- Wrapper for all centered content -->
            <img src="splash.png" alt="Cold Storage Queen" id="introImage">
            
            <div class="intro-text-block">
                <h1>Oh-em-gee, hi! 💋</h1>
                <h2>Your Cold Storage Queen 🧚‍♀️✨ is on high alert!</h2>
                <p>My fridge patrol just found a TOTAL disaster: ancient, unlabeled mystery gunk (was it... tuna?! 🤢). Expired since, like, the dawn of time! Cleopatra called, she wants her lunch back! 🏺🍣</p>
                <p>Seriously, babes, this fridge is NOT a haunted house for forgotten food! 👻🚫</p>
                <p class="emphasis-text">✨TRASHED. YEETED. GONE.✨</p>
                <p>The law: <strong>LABEL. YOUR. STUFF.</strong> Or it's next! 😉</p>
                <p>Help me sort this out & make the fridge sparkle! Kthxbye! 💖👑</p>
            </div>
                        <div id="startButtonsContainer" style="display: flex; flex-direction: column; gap: 10px; align-items: center; width: 100%; margin-top: 20px;">
                <button id="startButton" style="width: 90%; max-width: 380px;">Regular Mode: Timed Chaos!</button>
                <button id="startEndlessButton" style="width: 90%; max-width: 380px;">Endless Mode: Fridge Zen</button>
            </div>
        </div>
    </div>
<div id="gameHost" style="display: none;">
        <canvas id="gameCanvas"></canvas>
        <div id="gameTopHud">
    <div id="gameTimerDisplay">Time: 60s</div>
    <div id="scoreBoard">Score: 0 ✨</div>
    <div id="comboContainer">
        <div id="comboDisplay"></div>
        <div id="comboTimerBarContainer">
            <div id="comboTimerBar"></div>
        </div>
    </div>
</div>
        <div id="gameUiContainer">
            <div id="todaysDateDisplay" style="font-size: 0.9em; color: #785549; margin-bottom: 5px;">Today: MM/DD/YYYY</div>
            <div id="messageArea">Sorting is, like, SO important.</div>
            <div id="binsContainer">
                <div class="bin" id="bin-correctly-labeled" data-bin-type="correctly_labeled"><span>Label Perfection! ✨</span></div>
                <div class="bin" id="bin-name-missing" data-bin-type="name_missing"><span>Name Missing?</span></div>
                <div class="bin" id="bin-date-missing" data-bin-type="date_missing"><span>Date Missing?</span></div>
                <div class="bin" id="bin-expired" data-bin-type="expired"><span>Expired Babe!</span></div>
            </div>
        </div> <!-- This closes gameUiContainer -->
    </div> <!-- This closes gameHost -->
     <div id="gameOverScreen" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(255, 154, 158, 0.95); z-index: 2000; flex-direction: column; align-items: center; justify-content: center; text-align: center; color: #4A3B34; padding: 20px; box-sizing: border-box;">
       <div style="background-color: rgba(255, 255, 255, 0.98); padding: 40px; border-radius: 30px; box-shadow: 0 10px 30px rgba(80, 40, 40, 0.3); max-width: 600px;">
           <h1 style="color: #E91E63; font-size: clamp(2em, 5vw, 2.8em); margin-bottom: 15px;">Time's Up, Gorgeous! 💋</h1>
           <p id="finalScoreText" style="font-size: clamp(1.5em, 3.5vw, 2em); margin-bottom: 25px; color: #AD1457; font-weight: bold;">Your Score: 0 ✨</p>
           <p style="font-size: clamp(1em, 2.2vw, 1.3em); margin-bottom: 30px;">You slayed that fridge (almost)! Want another go?</p>
           <button id="playAgainButton" style="background-color: #FF69B4; color: white; padding: 15px 35px; border: none; border-radius: 12px; font-size: clamp(1.1em, 2.3vw, 1.4em); cursor: pointer; transition: background-color 0.3s ease, transform 0.2s ease; font-family: 'Comic Sans MS', 'Chalkboard SE', 'Marker Felt', sans-serif; box-shadow: 0 5px 8px rgba(0,0,0,0.15);">Play Again, Hun!</button>
       </div>
   </div>

   <script src="/trophies/trophies.js"></script>

    <script>

    let gameMode = 'regular'; // Default mode
    let itemsSortedCount = 0;

   const gameTimerDisplay = document.getElementById('gameTimerDisplay');
   const gameOverScreen = document.getElementById('gameOverScreen');
   const finalScoreText = document.getElementById('finalScoreText');
   const playAgainButton = document.getElementById('playAgainButton');

   const comboTimerBarContainer = document.getElementById('comboTimerBarContainer');
   const comboTimerBar = document.getElementById('comboTimerBar');
   let comboTimeoutStartTime = 0;

   // Game Timer
   const GAME_DURATION = 60; // seconds
   let gameTimeRemaining = GAME_DURATION;
   let gameTimerIntervalId = null;
   let isGameOver = false;

        const gameHost = document.getElementById('gameHost');
        const canvas = document.getElementById('gameCanvas');
        const ctx = canvas.getContext('2d');
        const scoreBoard = document.getElementById('scoreBoard');
        const messageArea = document.getElementById('messageArea');
        const binsElements = {
            name_missing: document.getElementById('bin-name-missing'),
            date_missing: document.getElementById('bin-date-missing'),
            correctly_labeled: document.getElementById('bin-correctly-labeled'), // New bin
            expired: document.getElementById('bin-expired')
        };

        const introScreen = document.getElementById('introScreen');
        const startButton = document.getElementById('startButton');
        const todaysDateDisplay = document.getElementById('todaysDateDisplay');
        const startEndlessButton = document.getElementById('startEndlessButton');

        let score = 0;
        let currentItem = null;
        let milkImage = new Image();
        milkImage.src = 'milk.png';
        let assetsLoaded = false;
        
        // --- AUDIO ---
        let currentVoiceAudio = null; // For voice lines
        let isVoicePlaying = false;
        const dingSound = new Audio('audio/ding.mp3'); // Make sure you have these files
        const buzzSound = new Audio('audio/buzz.mp3');
        const popSound = new Audio('audio/pop.mp3?v=1');


        let currentCombo = 0;
        const maxCombo = 10;
        const comboMessages = ["", "Cute.", "Okay!", "Good!", "Sweet!", "Amazing!", "Ice Cold", "ICONIC!!", "QUEEN!!!", "GODDESS 👑", "✨SLAY MAX✨"]; // Index 0 empty, 1 for x1 etc.
        let comboTimeoutId = null; // To reset combo if player is too slow
        const COMBO_TIMEOUT_DURATION = 4500; // 4 seconds to make the next correct move

        const comboDisplay = document.getElementById('comboDisplay');

        const bgMusic = new Audio('audio/fridgemusic.mp3');

        
            // --- BACKGROUND MUSIC SETUP ---
            bgMusic.loop = true;
            bgMusic.volume = 0.2; // Set to a low volume (0.0 to 1.0)
            // --- END BACKGROUND MUSIC SETUP ---


                const possibleItems = [
            {
                id: "milk", // Unique identifier
                displayName: "Milk", // What the player might call it (not for label)
                imageSrc: "milk.png", // Path to its image
                baseOwnerName: null // Specific owner, or null to pick randomly
            },
            {
                id: "sushi", // Unique identifier
                displayName: "Sushi", // What the player might call it (not for label)
                imageSrc: "sushi.png", // Path to its image
                baseOwnerName: null // Specific owner, or null to pick randomly
            },

                        {
                id: "yogurt", // Unique identifier
                displayName: "Yogurt", // What the player might call it (not for label)
                imageSrc: "yogurt.png", // Path to its image
                baseOwnerName: null // Specific owner, or null to pick randomly
            },

            
             {
                id: "pepsi", // Unique identifier
                displayName: "Pepsi", // What the player might call it (not for label)
                imageSrc: "pepsi.png", // Path to its image
                baseOwnerName: null // Specific owner, or null to pick randomly
            },

                        
             {
                id: "banana", // Unique identifier
                displayName: "Banana", // What the player might call it (not for label)
                imageSrc: "banana.png", // Path to its image
                baseOwnerName: null // Specific owner, or null to pick randomly
            },

                         {
                id: "salad", // Unique identifier
                displayName: "Salad", // What the player might call it (not for label)
                imageSrc: "salad.png", // Path to its image
                baseOwnerName: null // Specific owner, or null to pick randomly
            },

                                     {
                id: "whiskey", // Unique identifier
                displayName: "Whiskey", // What the player might call it (not for label)
                imageSrc: "whiskey.png", // Path to its image
                baseOwnerName: null // Specific owner, or null to pick randomly
            },
            
            {
                id: "pizza", // Unique identifier
                displayName: "Pizza", // What the player might call it (not for label)
                imageSrc: "pizza.png", // Path to its image
                baseOwnerName: null // Specific owner, or null to pick randomly
            },

                        {
                id: "wine", // Unique identifier
                displayName: "Wine", // What the player might call it (not for label)
                imageSrc: "wine.png", // Path to its image
                baseOwnerName: null // Specific owner, or null to pick randomly
            },

                                    {
                id: "cheese", // Unique identifier
                displayName: "Cheese", // What the player might call it (not for label)
                imageSrc: "cheese.png", // Path to its image
                baseOwnerName: null // Specific owner, or null to pick randomly
            },

                                                {
                id: "noodle", // Unique identifier
                displayName: "Noodles", // What the player might call it (not for label)
                imageSrc: "noodle.png", // Path to its image
                baseOwnerName: null // Specific owner, or null to pick randomly
            },
              {
                id: "hunny", // Unique identifier
                displayName: "Hunny", // What the player might call it (not for label)
                imageSrc: "hunny.png", // Path to its image
                baseOwnerName: null // Specific owner, or null to pick randomly
            },

            
               {
                id: "baguette", // Unique identifier
                displayName: "Baguette", // What the player might call it (not for label)
                imageSrc: "baguette.png", // Path to its image
                baseOwnerName: null // Specific owner, or null to pick randomly
            },

                           {
                id: "redbull", // Unique identifier
                displayName: "Redbull", // What the player might call it (not for label)
                imageSrc: "redbull.png", // Path to its image
                baseOwnerName: null // Specific owner, or null to pick randomly
            },


                 {
                id: "poison", // Unique identifier
                displayName: "Poison", // What the player might call it (not for label)
                imageSrc: "poison.png", // Path to its image
                baseOwnerName: null // Specific owner, or null to pick randomly
            },

             {
                id: "sandwich", // Unique identifier
                displayName: "Sandwich", // What the player might call it (not for label)
                imageSrc: "sandwich.png", // Path to its image
                baseOwnerName: null // Specific owner, or null to pick randomly
            },

                 {
                id: "kuromi", // Unique identifier
                displayName: "Kuromi", // What the player might call it (not for label)
                imageSrc: "kuromi.png", // Path to its image
                baseOwnerName: null // Specific owner, or null to pick randomly
            },

             {
                id: "protein", // Unique identifier
                displayName: "Protein", // What the player might call it (not for label)
                imageSrc: "protein.png", // Path to its image
                baseOwnerName: null // Specific owner, or null to pick randomly
            },

              {
                id: "strong", // Unique identifier
                displayName: "StrongZero", // What the player might call it (not for label)
                imageSrc: "strong.png", // Path to its image
                baseOwnerName: null // Specific owner, or null to pick randomly
            },

            {
                id: "cake", // Unique identifier
                displayName: "Cake", // What the player might call it (not for label)
                imageSrc: "cake.png", // Path to its image
                baseOwnerName: null // Specific owner, or null to pick randomly
            },

             {
                id: "beer", // Unique identifier
                displayName: "Beer", // What the player might call it (not for label)
                imageSrc: "beer.png", // Path to its image
                baseOwnerName: null // Specific owner, or null to pick randomly
            },


             {
                id: "controller", // Unique identifier
                displayName: "Controller", // What the player might call it (not for label)
                imageSrc: "controller.png", // Path to its image
                baseOwnerName: null // Specific owner, or null to pick randomly
            },

             {
                id: "waffle", // Unique identifier
                displayName: "Waffle", // What the player might call it (not for label)
                imageSrc: "waffle.png", // Path to its image
                baseOwnerName: null // Specific owner, or null to pick randomly
            },


              {
                id: "starbucks", // Unique identifier
                displayName: "Starbucks", // What the player might call it (not for label)
                imageSrc: "starbucks.png", // Path to its image
                baseOwnerName: null // Specific owner, or null to pick randomly
            },

                          {
                id: "broccoli", // Unique identifier
                displayName: "Broccoli", // What the player might call it (not for label)
                imageSrc: "broccoli.png", // Path to its image
                baseOwnerName: null // Specific owner, or null to pick randomly
            }
            
        ];

                // Store loaded images
        const itemImages = {}; 

         const funnyOwnerNames = [
            "Queen B", "Not Yours", "Snack Goblin",
            "Sir Reginald III", "Princess Sparklefart", "Captain Crunch",
            "Grob", "Saucy Susan", "Lord Leftovers", "Fran", "Stefano", "Veronica", "Meowgan",
            "Dr. Horrible", "Kenzo", "Miguel E. C.", "Mark Scout", "The A.C. Guy", "Jeff", "Ryu",
            "Leon", "Kuromi", "Mr. Asparago", "Sabrina C.", "D. Cooper", "F. Mulder", "Abby Normal",
            "El Barto", "W. Pooh"
        ];



      let assetsToLoad = possibleItems.length;
        let assetsLoadedCount = 0;
        // assetsLoaded flag remains the same

        function assetLoaded() {
            assetsLoadedCount++;
            if (assetsLoadedCount === assetsToLoad) {
                assetsLoaded = true;
                console.log("All item assets loaded, hun! ✨");
                // If game was waiting for assets, it can now start if startButton was clicked
            }
        }

        function preloadItemImages() {
            if (possibleItems.length === 0) { // Handle case with no items defined
                assetsLoaded = true; 
                return;
            }
            possibleItems.forEach(itemDef => {
                const img = new Image();
                img.onload = assetLoaded;
                img.onerror = () => {
                    console.error(`OMG, could not load image for ${itemDef.id} at ${itemDef.imageSrc}! Fix it, babe!`);
                    assetLoaded(); // Still count it as "processed" to not block game start
                };
                img.src = itemDef.imageSrc;
                itemImages[itemDef.id] = img;
            });
        }

        preloadItemImages();


        // --- DATE FORMATTING ---
        const dateFormats = [
            { format: (d) => `${(d.getMonth() + 1).toString().padStart(2, '0')}/${d.getDate().toString().padStart(2, '0')}/${d.getFullYear()}`, name: "MM/DD/YYYY" }, // 01/25/24
            { format: (d) => `${d.getDate()}-${d.toLocaleString('default', { month: 'short' })}-${d.getFullYear()}`, name: "DD-Mon-YYYY" }, // 25-Jan-2024
            { format: (d) => `${d.getFullYear()}.${(d.getMonth() + 1).toString().padStart(2, '0')}.${d.getDate().toString().padStart(2, '0')}`, name: "YYYY.MM.DD" }, // 2024.01.25
            { format: (d) => d.toLocaleDateString('en-GB'), name: "DD/MM/YYYY (UK)" }, // 25/01/2024
           { format: (d) => d.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' }), name: "Wkd, Mon D, YYYY" }

        ];

        function getRandomDateFormat(date) {
            if (!date) return "??.??";
            const randomFormatter = dateFormats[Math.floor(Math.random() * dateFormats.length)];
            return randomFormatter.format(date);
        }

        const ITEM_ORIGINAL_WIDTH = 1024;
        const ITEM_ORIGINAL_HEIGHT = 1024;
        const ITEM_SCALE_VS_CANVAS_WIDTH = 0.3; // Key: Item width as % of canvas width. ADJUST!

        const POST_IT_SCALE_VS_ITEM_DIMENSION = 0.4; // Post-it size relative to item's smaller dimension. ADJUST!
        const POST_IT_FONT_SCALE_VS_LABEL_HEIGHT = 0.16; // Font relative to post-it height
        const MIN_POST_IT_FONT_SIZE = 14;
        const MAX_POST_IT_FONT_SIZE = 16; // Capped at 18px for readability on a small note

        const LABEL_FONT_SIZE_FALLBACK = 16; // For the '?' if image load fails
        const labelTextColors = ['#000000', '#00008B', '#4B0082'];


        let currentlyOverlappedBinType = null;

         function checkItemOverlapWithBins() {
            if (!currentItem) return null;

            // Get the current item's visual bounding box ON THE CANVAS
            // Its x, y, width, height are canvas coordinates.
            // We need to translate these to viewport coordinates to compare with HTML bins.
            const canvasRect = canvas.getBoundingClientRect();
            const itemRectVP = { // Item's rectangle in Viewport coordinates
                left: canvasRect.left + currentItem.x,
                right: canvasRect.left + currentItem.x + currentItem.width,
                top: canvasRect.top + currentItem.y,
                bottom: canvasRect.top + currentItem.y + currentItem.height,
                centerX: canvasRect.left + currentItem.x + currentItem.width / 2,
                centerY: canvasRect.top + currentItem.y + currentItem.height / 2
            };

            let overlappedBin = null;

            for (const binType in binsElements) {
                const binEl = binsElements[binType];
                const binRectVP = binEl.getBoundingClientRect(); // Bin's rectangle in Viewport

                // Simple Overlap Check (AABB - Axis-Aligned Bounding Box)
                // Check if the item's *bottom part* is significantly over the bin
                const itemEffectiveBottom = itemRectVP.bottom - (currentItem.height * 0.2); // e.g., consider last 20% of item height for overlap
                const binEffectiveTop = binRectVP.top + (binRectVP.height * 0.2); // e.g., consider bin active once item crosses top 20% of bin

                if (
                    itemRectVP.left < binRectVP.right &&
                    itemRectVP.right > binRectVP.left &&
                    itemRectVP.top < binRectVP.bottom && // Original top check
                    itemEffectiveBottom > binRectVP.top // Item's bottom part is past bin's top
                    // && itemRectVP.bottom > binEffectiveTop // Alternative: item bottom passed a bit into the bin
                ) {
                    // More precise check: is the center of the item horizontally within the bin?
                    // This helps select the "most likely" bin if item slightly overlaps two.
                    if (itemRectVP.centerX > binRectVP.left && itemRectVP.centerX < binRectVP.right) {
                        overlappedBin = binType;
                        break; // Found the primary bin based on horizontal center
                    }
                    // Fallback if center not in any, but still overlapping
                    if (!overlappedBin) {
                        overlappedBin = binType;
                    }
                }
            }
            return overlappedBin;
        }

              // --- REPLACE your existing resizeCanvas function ---
        // --- REPLACE your existing resizeCanvas function ---
        function resizeCanvas() {
            const hostRect = gameHost.getBoundingClientRect();
            if (hostRect.width <= 0 || hostRect.height <= 0) { return false; }
            canvas.width = hostRect.width;
            canvas.height = hostRect.height;

            if (currentItem && !currentItem.isDragging) {
                let newImageDrawWidth = canvas.width * ITEM_SCALE_VS_CANVAS_WIDTH;
                let aspectRatio = ITEM_ORIGINAL_HEIGHT / ITEM_ORIGINAL_WIDTH;
                if (ITEM_ORIGINAL_WIDTH === 0) aspectRatio = 1;
                let newImageDrawHeight = newImageDrawWidth * aspectRatio; // Corrected: was newImageDrawWidth
                const maxPixelDrawSize = Math.min(canvas.width * 0.6, canvas.height * 0.5);
                const minPixelDrawSize = Math.max(80, canvas.width * 0.1);
                if (newImageDrawWidth > maxPixelDrawSize) { newImageDrawWidth = maxPixelDrawSize; newImageDrawHeight = newImageDrawWidth * aspectRatio; }
                if (newImageDrawHeight > maxPixelDrawSize) { newImageDrawHeight = maxPixelDrawSize; newImageDrawWidth = newImageDrawHeight / aspectRatio; }
                if (newImageDrawWidth < minPixelDrawSize) { newImageDrawWidth = minPixelDrawSize; newImageDrawHeight = newImageDrawWidth * aspectRatio; }
                if (newImageDrawHeight < minPixelDrawSize) { newImageDrawHeight = minPixelDrawSize; newImageDrawWidth = newImageDrawHeight / aspectRatio; }
                newImageDrawWidth = Math.max(1, newImageDrawWidth);
                newImageDrawHeight = Math.max(1, newImageDrawHeight);

                const newItemDisplayCanvas = createItemCanvas(
                    itemImages[currentItem.itemDefinition.id],
                    currentItem.nameForPostIt,         // Pass stored name for post-it
                    currentItem.dateObjForPostIt,      // Pass stored date object for post-it
                    currentItem.labelType,
                    newImageDrawWidth,
                    newImageDrawHeight,
                    currentItem.postItTargetSize, currentItem.postItWidthFactor, currentItem.postItHeightFactor,
                    currentItem.postItNoteRotation, currentItem.postItTextRotation,
                    currentItem.postItBiasXFactor, currentItem.postItBiasYFactor,
                    currentItem.postItJitterXFactor, currentItem.postItJitterYFactor,
                    // MODIFICATION: Pass stored display properties
                    currentItem.formattedExpDateString, 
                    currentItem.labelTextColor
                );

                if (newItemDisplayCanvas && newItemDisplayCanvas.width > 0 && newItemDisplayCanvas.height > 0) {
                    currentItem.imageCanvas = newItemDisplayCanvas;
                    currentItem.width = newItemDisplayCanvas.width; 
                    currentItem.height = newItemDisplayCanvas.height;
                    currentItem.targetY = canvas.height * 0.25 - currentItem.height / 2;
                    currentItem.x = canvas.width / 2 - currentItem.width / 2;
                    if (!currentItem.isFalling) { 
                        currentItem.y = currentItem.targetY;
                    }
                } else {
                    console.error("RESIZE: createItemCanvas failed. Post-it or item might be missing/incorrect.");
                }
            }
            return true;
        }

                function updateTodaysDateDisplay() {
            const today = new Date();
            const options = { year: 'numeric', month: 'short', day: 'numeric' }; // e.g., Jan 1, 2024
            todaysDateDisplay.textContent = `Today: ${today.toLocaleDateString(undefined, options)}`;
        }


        const sassyMessages = {
            correct: [
                { text: "Yasss, Queen! Nailed it! 💅", voice: "audio/correct1.mp3" },
                { text: "You're, like, a fridge sorting PRO! ✨", voice: "audio/correct2.mp3" },
                { text: "Slay that organization!", voice: "audio/correct3.mp3" },
                { text: "Buh-Bye 💋", voice: "audio/correct4.mp3" },
                { text: "Literal queen. 👑", voice: "audio/correct5.mp3" },
                { text: "You're a chilling machine!", voice: "audio/correct6.mp3" },
                { text: "Ice ice baby!", voice: "audio/correct7.mp3" }
            ],
            incorrect: [
                { text: "Oopsie! Not quite, sweetie. 😬", voice: "audio/incorrect1.mp3" },
                { text: "Hmm, let's re-think that one, babe. 🤔", voice: "audio/incorrect2.mp3" },
                { text: "Nope. Not even close! 🤔", voice: "audio/incorrect3.mp3" },
                { text: "Wrong bin, darling! 🤔", voice: "audio/incorrect4.mp3" },
                { text: "Nu-uh. Try again. 💅", voice: "audio/incorrect5.mp3" },
                { text: "Girl... really? 😒", voice: "audio/incorrect6.mp3" },
                { text: "Uh... No! 😒", voice: "audio/incorrect7.mp3" }
                
            ],
            spawn: [
                { text: "Ooh, what's this mystery item? 👀", voice: "audio/spawn1.mp3" },
                { text: "Fresh delivery! Where does this go, babe? 🚚", voice: "audio/spawn2.mp3" },
                { text: "Incoming drama. 🧊", voice: "audio/spawn3.mp3" },
                { text: "Who dis?", voice: "audio/spawn4.mp3" },
                { text: "Let's tidy up!", voice: "audio/spawn5.mp3" },
                { text: "Is it expired?", voice: "audio/spawn6.mp3" },
                { text: "Who left this?", voice: "audio/spawn7.mp3" }
            ],
            intro: [ 
                 { text: "Oh-em-gee hi 💋 It's your Cold Storage Queen 🧚‍♀️✨", voice: "audio/intro_greeting.mp3" }
            ]
        };




       function playSfx(sound) {
            sound.currentTime = 0;
            sound.play().catch(e => console.warn("SFX play failed", e));
        }

        function playVoice(voicePath) {
            if (isVoicePlaying || !voicePath) { // If a voice is already playing OR no voice path, skip
                return;
            }
            
            if (currentVoiceAudio) { // Should not happen if isVoicePlaying is true, but safety
                currentVoiceAudio.pause();
            }

            isVoicePlaying = true;
            currentVoiceAudio = new Audio(voicePath);
            currentVoiceAudio.play().catch(e => {
                console.warn("Voice play failed", e);
                isVoicePlaying = false; // Reset if play fails
            });

            currentVoiceAudio.onended = () => {
                isVoicePlaying = false;
                currentVoiceAudio = null;
            };
            currentVoiceAudio.onerror = () => { // Also handle errors during playback
                isVoicePlaying = false;
                currentVoiceAudio = null;
            };
        }


        function getRandomMessage(type) {
            const messagesArray = sassyMessages[type];
            if (!messagesArray || messagesArray.length === 0) {
                return { text: "Hmm, I'm speechless!", voice: null };
            }
            return messagesArray[Math.floor(Math.random() * messagesArray.length)];
        }

        function updateScore(change) { // 'change' is points for regular mode
            if (gameMode === 'regular') {
                score += change;
                scoreBoard.textContent = `Score: ${score} ✨`;
                
            if (score >= 200) {
            unlockTrophy("fridge_frozen");
            }

            } else { // Endless mode
                // 'change' is ignored for endless mode score display, it's based on itemsSortedCount
                scoreBoard.textContent = `Sorted: ${itemsSortedCount} 💅`;
            }
        }

        function showMessageAndPlayVoice(messageObject) {
            messageArea.textContent = messageObject.text;
            playVoice(messageObject.voice); // This will now respect the isVoicePlaying flag
        }

        function formatDate(date) {
            if (!date) return "??.??";
            let day = date.getDate().toString().padStart(2, '0');
            let month = (date.getMonth() + 1).toString().padStart(2, '0');
            return `${day}/${month}`;
        }
function createItemCanvas(baseImage, nameForPostIt, dateObjForPostIt, labelType, 
                                targetItemDrawWidth, targetItemDrawHeight,
                                // Pre-calculated random properties for the post-it
                                p_targetSize, p_widthFactor, p_heightFactor, 
                                p_noteRotation, p_textRotation,
                                p_biasXFactor, p_biasYFactor, 
                                p_jitterXFactor, p_jitterYFactor,
                                // NEW PARAMETERS FOR CONSISTENT DISPLAY
                                p_formattedExpDateString, p_labelTextColor) { // Added new params
            
    targetItemDrawWidth = Math.max(1, targetItemDrawWidth || 100);
    targetItemDrawHeight = Math.max(1, targetItemDrawHeight || 100);

    const itemCanvas = document.createElement('canvas');
    const itemCtx = itemCanvas.getContext('2d');
    
    const safe_p_targetSize = Math.max(20, p_targetSize || POST_IT_SCALE_VS_ITEM_DIMENSION * Math.min(targetItemDrawWidth, targetItemDrawHeight) );

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
    } else {
        itemCtx.imageSmoothingEnabled = true;
        itemCtx.fillStyle = 'grey';
        itemCtx.fillRect(itemImageX, itemImageY, targetItemDrawWidth, targetItemDrawHeight);
        itemCtx.fillStyle = 'white'; itemCtx.textAlign = 'center'; itemCtx.textBaseline = 'middle';
        itemCtx.font = `${LABEL_FONT_SIZE_FALLBACK * 1.5}px Arial`; 
        itemCtx.fillText("?", itemImageX + targetItemDrawWidth/2, itemImageY + targetItemDrawHeight/2);
        itemCtx.imageSmoothingEnabled = false; 
    }

    itemCtx.imageSmoothingEnabled = true; 
    itemCtx.imageSmoothingQuality = 'high';

    itemCtx.save(); 
    // const horizontalSide = Math.random() < 0.5 ? -1 : 1; // Already decided and passed via factors
    // const verticalSide = Math.random() < 0.5 ? -1 : 1;   // Already decided
    
    const finalSideBiasX = targetItemDrawWidth * p_biasXFactor + labelWidth * p_biasXFactor * 0.2;
    const finalSideBiasY = targetItemDrawHeight * p_biasYFactor + labelHeight * p_biasYFactor * 0.2;
    const finalRandomJitterX = targetItemDrawWidth * p_jitterXFactor;
    const finalRandomJitterY = targetItemDrawHeight * p_jitterYFactor;

    const labelCenterX = itemImageX + targetItemDrawWidth / 2 + finalSideBiasX + finalRandomJitterX;
    const labelCenterY = itemImageY + targetItemDrawHeight / 2 + finalSideBiasY + finalRandomJitterY;
    
    itemCtx.translate(labelCenterX, labelCenterY); 
    itemCtx.rotate(p_noteRotation);
    
    itemCtx.globalAlpha = 0.93; 
    itemCtx.fillStyle = '#FFFACD'; 
    itemCtx.shadowColor = 'rgba(0,0,0,0.25)'; 
    itemCtx.shadowBlur = 8;
    itemCtx.shadowOffsetX = Math.cos(p_noteRotation + Math.PI/4) * 3;
    itemCtx.shadowOffsetY = Math.sin(p_noteRotation + Math.PI/4) * 3;
    
    itemCtx.fillRect(-labelWidth / 2, -labelHeight / 2, labelWidth, labelHeight); 
    itemCtx.shadowColor = 'transparent'; 
    
    const currentPostItDimForSpeckles = Math.min(labelWidth, labelHeight);
    const speckleCount = Math.floor((labelWidth * labelHeight) / (currentPostItDimForSpeckles < 80 ? 150 : 100) );
    const speckleColors = ['rgba(0,0,0,0.10)','rgba(0,0,0,0.09)','rgba(80,60,20,0.07)']; 
    for (let i = 0; i < speckleCount; i++) {
        const x = (Math.random() - 0.5) * labelWidth * 0.95;
        const y = (Math.random() - 0.5) * labelHeight * 0.95;
        const radius = Math.random() * 1.0 + 0.5;
        itemCtx.beginPath(); itemCtx.arc(x, y, radius, 0, Math.PI * 2);
        itemCtx.fillStyle = speckleColors[Math.floor(Math.random() * speckleColors.length)]; // Speckles can still be random
        itemCtx.fill();
    }
            
    itemCtx.globalAlpha = 1.0; 
    itemCtx.strokeStyle = '#E0DCBE'; itemCtx.lineWidth = 1;
    itemCtx.strokeRect(-labelWidth / 2 + 0.5, -labelHeight / 2 + 0.5, labelWidth - 1, labelHeight - 1);

    itemCtx.fillStyle = p_labelTextColor; // MODIFICATION: Use passed-in color
    const calculatedFontSize = labelHeight * POST_IT_FONT_SCALE_VS_LABEL_HEIGHT;
    const postItFontSize = Math.max(MIN_POST_IT_FONT_SIZE, Math.min(MAX_POST_IT_FONT_SIZE, calculatedFontSize));
    itemCtx.font = `${postItFontSize}px Schoolbell, cursive`;
    itemCtx.textAlign = 'center'; itemCtx.textBaseline = 'middle';
            
    itemCtx.save();
    itemCtx.rotate(p_textRotation);
            
    let labelText1 = "";
    let labelText2 = "";

    // MODIFICATION: Use passed-in nameForPostIt, dateObjForPostIt, and p_formattedExpDateString
    if (labelType === 'both' || labelType === 'name_only') {
        labelText1 = nameForPostIt ? `${nameForPostIt}` : "Owner: ???";
    } else if (labelType === 'date_only') {
        labelText1 = " ";
    } else { // labelType === 'none'
        labelText1 = "Who dis?!";
    }

    if (labelType === 'both' || labelType === 'date_only') {
        labelText2 = dateObjForPostIt ? `${p_formattedExpDateString}` : "Exp: ???";
    } else if (labelType === 'name_only') {
        labelText2 = " ";
    } else { // labelType === 'none'
        labelText2 = "No info!";
    }

    const lineOffset = postItFontSize * 1.3; 
    itemCtx.fillText(labelText1, 0, -lineOffset / 2);
    itemCtx.fillText(labelText2, 0, lineOffset / 2 + (postItFontSize * 0.05));
    itemCtx.restore(); 

    itemCtx.restore(); 
    return itemCanvas;
}
      function spawnNewItem() {
        if (isGameOver) return;
            if (!assetsLoaded || gameHost.style.display === 'none') {
                setTimeout(spawnNewItem, 100); return;
            }
            if (possibleItems.length === 0) { 
                console.error("No items to spawn, babe! Add some to possibleItems."); return; 
            }

            if (canvas.width === 0 || canvas.height === 0) {
                if (!resizeCanvas()) {
                    console.warn("spawnNewItem: Main canvas zero, resize failed. Retrying spawn.");
                    setTimeout(spawnNewItem, 100);
                    return;
                }
            }
            
            let currentItemDrawWidth = canvas.width * ITEM_SCALE_VS_CANVAS_WIDTH;
            let aspectRatio = ITEM_ORIGINAL_HEIGHT / ITEM_ORIGINAL_WIDTH;
            if (ITEM_ORIGINAL_WIDTH === 0) aspectRatio = 1;
            let currentItemDrawHeight = currentItemDrawWidth * aspectRatio;

            const maxPixelDrawSize = Math.min(canvas.width * 0.6, canvas.height * 0.5);
            const minPixelDrawSize = Math.max(80, canvas.width * 0.1);

            if (currentItemDrawWidth > maxPixelDrawSize) { currentItemDrawWidth = maxPixelDrawSize; currentItemDrawHeight = currentItemDrawWidth * aspectRatio; }
            if (currentItemDrawHeight > maxPixelDrawSize) { currentItemDrawHeight = maxPixelDrawSize; currentItemDrawWidth = currentItemDrawHeight / aspectRatio; }
            if (currentItemDrawWidth < minPixelDrawSize) { currentItemDrawWidth = minPixelDrawSize; currentItemDrawHeight = currentItemDrawWidth * aspectRatio; }
            if (currentItemDrawHeight < minPixelDrawSize) { currentItemDrawHeight = minPixelDrawSize; currentItemDrawWidth = currentItemDrawHeight / aspectRatio; }

            currentItemDrawWidth = Math.max(1, currentItemDrawWidth);
            currentItemDrawHeight = Math.max(1, currentItemDrawHeight);

            playSfx(popSound);
            showMessageAndPlayVoice(getRandomMessage('spawn'));

            const itemDefinition = possibleItems[Math.floor(Math.random() * possibleItems.length)];
            const itemImage = itemImages[itemDefinition.id];
            
            // --- NEW LOGIC FOR ITEM GENERATION ---
            let actualOwnerNameForItem = null;
            let actualExpirationDateForItem = null;
            let isActuallyExpired = false;
            let nameForPostIt = null;
            let dateObjForPostIt = null;
            let labelType = '';
            let correctBin = '';

            // Helper to generate a date object
            function generateDateObject(options = { makeExpired: false, makeFuture: true }) {
                let date = new Date();
                let daysOffset;
                if (options.makeExpired) {
                    daysOffset = -(Math.random() * 60 + 10); // -10 to -70 days
                } else { // makeFuture (default non-expired)
                    daysOffset = (Math.random() * 60 + 5); // +5 to +65 days (ensures not expired today)
                }
                date.setDate(date.getDate() + Math.floor(daysOffset));
                return date;
            }
            // Helper to get a random owner name
            function getRandomOwnerName() {
                return funnyOwnerNames[Math.floor(Math.random() * funnyOwnerNames.length)];
            }

            // Determine item category (equal probability for now)
            const categoryRoll = Math.random();

            if (categoryRoll < 0.25) { // 1. CORRECTLY_LABELED (Name + Non-Expired Date)
                actualOwnerNameForItem = getRandomOwnerName();
                actualExpirationDateForItem = generateDateObject({ makeFuture: true });
                isActuallyExpired = false;
                
                nameForPostIt = actualOwnerNameForItem;
                dateObjForPostIt = actualExpirationDateForItem;
                labelType = 'both';
                correctBin = 'correctly_labeled';

            } else if (categoryRoll < 0.50) { // 2. EXPIRED
                isActuallyExpired = true;
                actualExpirationDateForItem = generateDateObject({ makeExpired: true });
                dateObjForPostIt = actualExpirationDateForItem;

                // Expired items can optionally have a name
                if (Math.random() < 0.7) { // 70% chance of having a name
                    actualOwnerNameForItem = getRandomOwnerName();
                    nameForPostIt = actualOwnerNameForItem;
                    labelType = 'both';
                } else {
                    labelType = 'date_only';
                }
                correctBin = 'expired';

            } else if (categoryRoll < 0.75) { // 3. NAME_MISSING (Has Date, Not Expired)
                actualOwnerNameForItem = null; // Explicitly no name
                actualExpirationDateForItem = generateDateObject({ makeFuture: true });
                isActuallyExpired = false;

                nameForPostIt = null;
                dateObjForPostIt = actualExpirationDateForItem;
                labelType = 'date_only';
                correctBin = 'name_missing';

            } else { // 4. DATE_MISSING (Has Name, Not Expired)
                actualOwnerNameForItem = getRandomOwnerName();
                actualExpirationDateForItem = null; // Explicitly no date
                isActuallyExpired = false; // Cannot be expired if no date

                nameForPostIt = actualOwnerNameForItem;
                dateObjForPostIt = null;
                labelType = 'name_only';
                correctBin = 'date_missing';
            }
            // --- END NEW LOGIC FOR ITEM GENERATION ---

            if (itemDefinition.id === "hunny" && actualOwnerNameForItem !== null) {
                // This means it's a hunny jar AND it was *intended* to have an owner name based on its category.
                if (Math.random() < 0.4) {
                    actualOwnerNameForItem = "W. Pooh";
                    nameForPostIt = "W. Pooh";

                }
            }
            // --- SPECIAL EXCEPTION LOGIC ---
            // Check if the item is labeled "Not Yours" AND is NOT actually expired
            if (dateObjForPostIt != null && nameForPostIt === "Not Yours" && !isActuallyExpired) {
                // Even if it has a date, the Queen considers "Not Yours" as effectively missing a name
                // for sorting purposes unless it's expired (expired items go to expired bin regardless of name).
                correctBin = 'name_missing';
            }


            // else if (nameForPostIt === "Fran" && dateObjForPostIt && dateObjForPostIt.getDay() === 5 /* Friday */) {
            //     if (!isActuallyExpired) {
            //        correctBin = 'correctly_labeled'; // Fran gets a pass on Fridays!
            //        console.log("QUEEN'S EXCEPTION: It's Fran's Friday treat!");
            //     }
            // }
            // --- END SPECIAL EXCEPTION LOGIC ---





            // 3. Generate formatted date string and text color BASED ON VALUES TO DISPLAY
            const chosenLabelTextColor = labelTextColors[Math.floor(Math.random() * labelTextColors.length)];
            const formattedExpDateStringToDisplay = dateObjForPostIt ? getRandomDateFormat(dateObjForPostIt) : "??.??";
            
            // Determine post-it physical random properties
            const targetPostItSizeForThisItem = Math.max(20, Math.min(currentItemDrawWidth, currentItemDrawHeight) * POST_IT_SCALE_VS_ITEM_DIMENSION);
            const postItRandomWidthFactor = (Math.random() - 0.5) * 0.2;
            const postItRandomHeightFactor = (Math.random() - 0.5) * 0.2;
            const postItRotation = (Math.random() - 0.5) * 0.25; 
            const textRotation = (Math.random() - 1) * 0.05;   
            const horizontalSide = Math.random() < 0.5 ? -1 : 1;
            const verticalSide = Math.random() < 0.5 ? -1 : 1;
            const postItBiasXFactor = horizontalSide * 0.20; 
            const postItBiasYFactor = verticalSide * 0.20;
            const postItJitterXFactor = (Math.random() - 0.5) * 0.05;
            const postItJitterYFactor = (Math.random() - 0.5) * 0.05;

            // 4. Create the item canvas
           const itemDisplayCanvas = createItemCanvas(
                itemImage, 
                nameForPostIt,      // Name to actually put on the post-it (could be null)
                dateObjForPostIt,   // Date object to actually use for post-it (could be null)
                labelType,
                currentItemDrawWidth, 
                currentItemDrawHeight,
                targetPostItSizeForThisItem, postItRandomWidthFactor, postItRandomHeightFactor,
                postItRotation, textRotation, postItBiasXFactor, postItBiasYFactor,
                postItJitterXFactor, postItJitterYFactor,
                // NEW properties for consistent display
                formattedExpDateStringToDisplay, 
                chosenLabelTextColor
            );
            
            if (!itemDisplayCanvas || itemDisplayCanvas.width === 0 || itemDisplayCanvas.height === 0) {
                console.error("SPAWN: createItemCanvas failed to produce a valid canvas. Retrying.");
                setTimeout(spawnNewItem, 100); 
                return;
            }

            // 5. Store all relevant info in currentItem
            currentItem = {
                imageCanvas: itemDisplayCanvas,
                actualOwnerName: actualOwnerNameForItem, 
                actualExpirationDate: actualExpirationDateForItem, 
                isActuallyExpired: isActuallyExpired, 
                
                // Properties for display consistency on resize
                nameForPostIt: nameForPostIt, 
                dateObjForPostIt: dateObjForPostIt, 
                formattedExpDateString: formattedExpDateStringToDisplay, 
                labelTextColor: chosenLabelTextColor, 
                labelType: labelType, 

                itemDefinition: itemDefinition, 
                correctBin: correctBin, 
                width: itemDisplayCanvas.width, 
                height: itemDisplayCanvas.height,
                x: canvas.width / 2 - itemDisplayCanvas.width / 2,
                y: -(itemDisplayCanvas.height), 
                targetY: canvas.height * 0.32 - itemDisplayCanvas.height / 2,
                isDragging: false, isFalling: true, dragOffsetX: 0, dragOffsetY: 0,
                dragRotation: 0, lastMouseX: 0,

                postItTargetSize: targetPostItSizeForThisItem,
                postItWidthFactor: postItRandomWidthFactor,
                postItHeightFactor: postItRandomHeightFactor,
                postItNoteRotation: postItRotation,
                postItTextRotation: textRotation,
                postItBiasXFactor: postItBiasXFactor,
                postItBiasYFactor: postItBiasYFactor,
                postItJitterXFactor: postItJitterXFactor,
                postItJitterYFactor: postItJitterYFactor
            };
            setCanvasCursor();
        }

        function drawItem() {
            if (!currentItem) return;

            ctx.save(); // Save the current context state

            // Translate to the center of the item for rotation
            const itemCenterX = currentItem.x + currentItem.width / 2;
            const itemCenterY = currentItem.y + currentItem.height / 2;
            ctx.translate(itemCenterX, itemCenterY);

            // Apply the drag rotation
            ctx.rotate(currentItem.dragRotation);

            // Draw the item image centered at the new (0,0) - which was its center
            ctx.drawImage(
                currentItem.imageCanvas, 
                -currentItem.width / 2, 
                -currentItem.height / 2, 
                currentItem.width, 
                currentItem.height
            );

            ctx.restore(); // Restore the context to its state before this item's transformations
        }

        function updateItemPosition() {
            if (!currentItem || currentItem.isDragging) return;
            if (currentItem.isFalling) {
                const dy = currentItem.targetY - currentItem.y;
                if (Math.abs(dy) < 1) {
                    currentItem.y = currentItem.targetY; currentItem.isFalling = false;
                        // === Add pop animation ===
    canvas.classList.remove('item-pop'); // Reset
    void canvas.offsetWidth; // Force reflow to allow re-adding
    canvas.classList.add('item-pop');
                     if (!currentItem.isDragging) canvas.style.cursor = 'grab';
                } else {
                    currentItem.y += dy * 0.08;
                }
            }
        }
        
        function getBinUnderMouse(x, y) {
            const binElementsArray = Object.values(binsElements);
            for (const binEl of binElementsArray) {
                const rect = binEl.getBoundingClientRect();
                if (x >= rect.left && x <= rect.right && y >= rect.top && y <= rect.bottom) {
                    return binEl.dataset.binType;
                }
            }
            return null;
        }

       // --- CURSOR & DRAGGING REFINEMENTS ---
        function setCanvasCursor() {
            if (!currentItem || gameHost.style.display === 'none') {
                canvas.style.cursor = 'grab';
                return;
            }
            if (currentItem.isDragging) {
                canvas.style.cursor = 'grabbing';
            } else if (!currentItem.isFalling) { // If item is stationary
                // Check if mouse is over the item (needed for initial state and non-dragging hover)
                // This requires knowing mouse position; usually handled by mousemove.
                // For now, if it's draggable, set to 'grab'.
                canvas.style.cursor = 'grab';
            } else {
                canvas.style.cursor = 'grab'; // If falling or no item
            }
        }


        canvas.addEventListener('mousedown', (e) => {
            // REMOVE or COMMENT OUT: currentItem.isFalling
            // if (!currentItem || currentItem.isFalling || e.button !== 0) return;
            if (isGameOver || !currentItem || e.button !== 0) return; // Allow grabbing even if falling

            const mouseX = e.offsetX;
            const mouseY = e.offsetY;

            if (mouseX >= currentItem.x && mouseX <= currentItem.x + currentItem.width &&
                mouseY >= currentItem.y && mouseY <= currentItem.y + currentItem.height) {
                
                currentItem.isFalling = false; // Stop falling animation immediately if grabbed
                currentItem.isDragging = true;
                currentItem.dragOffsetX = mouseX - currentItem.x;
                currentItem.dragOffsetY = mouseY - currentItem.y;
                setCanvasCursor(); 
            }
        });


        
        document.addEventListener('mousemove', (e) => {
            if (isGameOver || !currentItem || !currentItem.isDragging) {
                // ... (existing logic to remove .drag-over class from bins) ...
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

            // === LIFELIKE DRAG ROTATION LOGIC ===
            if (currentItem.lastMouseX !== 0) { // Avoid jump on first move
                const dx = mouseXInCanvas - currentItem.lastMouseX;
                // dx is the horizontal change in mouse position since last frame
                
                // Tilt factor: how much it tilts per pixel of mouse movement
                // Keep this small for a subtle effect
                const tiltFactor = 0.01; // Radians per pixel
                let targetRotation = dx * tiltFactor;

                // Max tilt: prevent it from spinning wildly
                const maxTilt = 0.35; // Radians (approx 14 degrees)
                targetRotation = Math.max(-maxTilt, Math.min(maxTilt, targetRotation));
                
                // Smoothly interpolate to the target rotation
                // Higher factor = faster snap to target tilt, lower = more "lag"
                currentItem.dragRotation += (targetRotation - currentItem.dragRotation) * 0.2; 
            }
            currentItem.lastMouseX = mouseXInCanvas;
            // === END LIFELIKE DRAG ROTATION LOGIC ===

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
            if (isGameOver || !currentItem || !currentItem.isDragging || e.button !== 0) return;
            
            const droppedOnBinType = checkItemOverlapWithBins(); // Use overlap for drop decision too

            // Clear any drag-over visual state from bins
            if (currentlyOverlappedBinType) { // Use the state variable
                binsElements[currentlyOverlappedBinType].classList.remove('drag-over');
                currentlyOverlappedBinType = null;
            }

            currentItem.isDragging = false;
            
            // const droppedBinType = getBinUnderMouse(e.clientX, e.clientY); // OLD WAY
            // NOW use droppedOnBinType determined by overlap

            if (droppedOnBinType) {
                if (droppedOnBinType === currentItem.correctBin) {

                if (
                droppedOnBinType === 'expired' &&
                currentItem.nameForPostIt === 'Kenzo'
                ) {
                unlockTrophy("fridge_kenzo");
                }

                 if (currentItem.nameForPostIt === "W. Pooh" && currentItem.itemDefinition.id === "hunny") {
                        unlockTrophy("fridge_hunny"); 
                    }

                if (gameMode === 'regular') {
                        updateScore(10); // Add points
                        increaseCombo();
                    } else { // Endless mode
                        itemsSortedCount++;
                        updateScore(0); // Call to update display (which uses itemsSortedCount)
                    }
                    showMessageAndPlayVoice(getRandomMessage('correct'));
                    playSfx(dingSound);
                    currentItem = null; 
                    setTimeout(spawnNewItem, 800);
                } else {
                    // ... (incorrect drop logic)
                    updateScore(-5);
                    resetCombo();
                    showMessageAndPlayVoice(getRandomMessage('incorrect'));
                    playSfx(buzzSound);
                    currentItem.x = canvas.width / 2 - currentItem.width / 2;
                    currentItem.y = currentItem.targetY;
                    currentItem.isFalling = false; 
                }
            } else { 
                // ... (not dropped on a bin logic)
                if (currentItem) {
                    currentItem.x = canvas.width / 2 - currentItem.width / 2;
                    currentItem.y = currentItem.targetY;
                    currentItem.isFalling = false;
                }
            }
            setCanvasCursor(); 
        });
        canvas.addEventListener('mouseleave', () => { 
             if (!currentItem || !currentItem.isDragging) {
                 canvas.style.cursor = 'default';
             }
             // If dragging, cursor remains 'grabbing' due to document listener
        });
   function gameLoop() {
       ctx.clearRect(0, 0, canvas.width, canvas.height);
       if (assetsLoaded && currentItem) {
           if (!isGameOver) { // Only update and draw if game is not over
               updateItemPosition();
               drawItem();
           }
       }

       // Update Combo Timer Visual
       if (currentCombo > 0 && comboTimeoutId && !isGameOver) { // Also check !isGameOver
           const elapsedComboTime = Date.now() - comboTimeoutStartTime;
           let remainingComboPercentage = 1 - (elapsedComboTime / COMBO_TIMEOUT_DURATION);
           remainingComboPercentage = Math.max(0, remainingComboPercentage); // Clamp at 0
           comboTimerBar.style.width = `${remainingComboPercentage * 100}%`;
       } else {
           // Ensure the bar is hidden if no combo or game over
           if (comboTimerBarContainer.style.display !== 'none') {
               comboTimerBarContainer.style.display = 'none';
           }
       }
       requestAnimationFrame(gameLoop);
   }


    function initiateGameStartLogic() {
            introScreen.style.display = 'none';
            gameHost.style.display = 'flex';
            gameOverScreen.style.display = 'none';

            // Reset scores and counts
            score = 0;
            itemsSortedCount = 0;
            isGameOver = false; // Ensure game over state is reset

            // Update UI elements based on game mode
            if (gameMode === 'regular') {
                scoreBoard.textContent = `Score: 0 ✨`;
                gameTimerDisplay.style.display = 'block'; // Make sure timer is visible
                finalScoreText.style.display = 'block'; // For game over screen
            } else { // Endless mode
                scoreBoard.textContent = `Sorted: 0 💅`;
                gameTimerDisplay.textContent = `Time: ∞ ✨`;
                gameTimerDisplay.style.display = 'block'; // Or 'none' if you prefer to hide it
                finalScoreText.style.display = 'none'; // No final score in endless
            }

                   function attemptActualGameStart() {
                if (resizeCanvas()) {
                    startGameTimer(); // Will now adapt to gameMode
                    updateTodaysDateDisplay();
                    resetCombo(); // Combos are fun in both modes!

                    bgMusic.currentTime = 0; // Rewind music
                    bgMusic.play().catch(error => {
                        console.warn("Background music couldn't play automatically, babe.", error);
                    });
                    
                    const silentSound = new Audio("data:audio/wav;base64,UklGRigAAABXQVZFZm10IBIAAAABAAEARKwAAIhYAQACABAAAABkYXRhAgAAAAEA");
                    silentSound.play().catch(()=>{});

                    const checkAssetsAndStartLoop = () => {
                        if (assetsLoaded) {
                            spawnNewItem(); 
                            gameLoop();
                        } else {
                            setTimeout(checkAssetsAndStartLoop, 100); 
                        }
                    };
                    checkAssetsAndStartLoop();
                } else {
                    requestAnimationFrame(attemptActualGameStart); 
                }
            }
            requestAnimationFrame(attemptActualGameStart); 
        }

               startButton.addEventListener('click', () => {
            gameMode = 'regular';
            initiateGameStartLogic();
        });

        // NEW: Event listener for the Endless Mode button
        startEndlessButton.addEventListener('click', () => {
            gameMode = 'endless';
            initiateGameStartLogic();
        });


        window.addEventListener('resize', () => {
            resizeCanvas();
            // If an item exists, re-center it or adjust its position after resize
            if (currentItem && !currentItem.isDragging && !isGameOver) {
                currentItem.x = canvas.width / 2 - currentItem.width / 2;
                if (!currentItem.isFalling) { // If it was stationary, update its Y based on new target
                    currentItem.y = currentItem.targetY;
                }
                // If falling, its current Y might be off, but targetY is updated, so it will adjust
            }
            setCanvasCursor(); // Update cursor after resize
        });

        if (!milkImage.src) {
             messageArea.textContent = "Hmm, looks like we're having trouble loading images, sweetie. Try refreshing?";
        }



         function updateComboDisplay() {
            if (currentCombo > 0) {
                let displayText = `x${currentCombo}`;
                if (currentCombo >= maxCombo) {
                    displayText = comboMessages[maxCombo] || `✨MAX COMBO x${currentCombo}✨`; // Use specific message or default
                } else if (comboMessages[currentCombo]) {
                    displayText = `${comboMessages[currentCombo]} (x${currentCombo})`;
                }
                
             comboDisplay.textContent = displayText;
               comboDisplay.classList.add('active'); 
               comboDisplay.classList.remove('pop'); 
               void comboDisplay.offsetWidth; 
               comboDisplay.classList.add('pop'); 
           } else {
               comboTimerBarContainer.style.display = 'none'; // <<< ADD THIS LINE
               comboDisplay.textContent = "";
               comboDisplay.classList.remove('active'); 
           }
        }

   function increaseCombo() {
           clearTimeout(comboTimeoutId); 

           if (currentCombo < maxCombo) {
               currentCombo++;
           } else {
               currentCombo = maxCombo; 
           }

           if (currentCombo === maxCombo) {
    unlockTrophy("fridge_slaymax");
}

           comboTimeoutStartTime = Date.now(); // <<< ADD THIS LINE
           comboTimerBarContainer.style.display = 'block'; // <<< ADD THIS LINE
           updateComboDisplay();

           comboTimeoutId = setTimeout(() => {
               resetCombo();
           }, COMBO_TIMEOUT_DURATION);
       }

       function resetCombo() {
           clearTimeout(comboTimeoutId); 
           currentCombo = 0;
           comboTimerBarContainer.style.display = 'none'; // <<< ADD THIS LINE
           if(comboTimerBar) comboTimerBar.style.width = '100%'; // <<< ADD THIS LINE (reset bar visual)
           updateComboDisplay();
       }

            function startGameTimer() {
            if (gameTimerIntervalId) clearInterval(gameTimerIntervalId);
            
            if (gameMode === 'regular') {
                isGameOver = false; // Explicitly set for regular mode start
                gameTimeRemaining = GAME_DURATION;
                gameTimerDisplay.textContent = `Time: ${gameTimeRemaining}s`;
                gameTimerDisplay.style.display = 'block';

                gameTimerIntervalId = setInterval(() => {
                    gameTimeRemaining--;
                    gameTimerDisplay.textContent = `Time: ${gameTimeRemaining}s`;
                    if (gameTimeRemaining <= 0) {
                        endGame();
                    }
                }, 1000);
            } else { // Endless mode
                isGameOver = false; // Endless mode doesn't end by time
                gameTimerDisplay.textContent = `Time: ∞ ✨`;
                gameTimerDisplay.style.display = 'block'; // Or 'none'
                // No interval needed for endless timer
            }
        }

        function endGame() {
            // Game over logic should only run for regular mode or if explicitly called
            if (gameMode !== 'regular' && gameTimeRemaining > 0) return; // Prevent accidental call in endless unless timer ran out (which it shouldn't)
            if (isGameOver) return; // Prevent multiple calls

            isGameOver = true;
            clearInterval(gameTimerIntervalId);
            if (gameMode === 'regular') {
                gameTimerDisplay.textContent = "Time's Up!";
            }
            
            if(currentItem) currentItem = null; 
            ctx.clearRect(0, 0, canvas.width, canvas.height); 
            
            if (gameMode === 'regular') {
                finalScoreText.textContent = `Your Score: ${score} ✨`;
                gameOverScreen.style.display = 'flex';
            }
            
            bgMusic.pause(); 
            resetCombo(); 
        }

     // MODIFICATION to playAgainButton event listener
        playAgainButton.addEventListener('click', () => {
            // This button is only shown after a 'regular' game ends.
            // So, restarting will be in 'regular' mode by default,
            // or more robustly, just re-call initiateGameStartLogic
            // which respects the currently set gameMode.
            // If we always want 'play again' to be regular, set gameMode here.
            // For now, it will restart in the mode that just ended (which is 'regular').
            
            // gameMode = 'regular'; // Uncomment if "Play Again" should ALWAYS be regular mode.
                                  // Otherwise, it uses the mode from the previous session.
                                  // Since this screen only appears for regular mode, it's fine.
            initiateGameStartLogic(); 
        });
    </script>


</body>
</html>