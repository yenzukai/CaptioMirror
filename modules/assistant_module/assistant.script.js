window.addEventListener('load', async function() {
    if (userPreferences.assistant_active) {
        startListening();
    }
});

const recognition = new (window.SpeechRecognition || window.webkitSpeechRecognition)();
const assistantName = userPreferences.assistant.assistant_name;
const voiceAssistant = userPreferences.assistant.voice_assistant;
const assistantStyle = userPreferences.assistant.assistant_style;
let idleTimer;
let isSpeaking = false;
let isListening = false;
let isMusicPlaying = false; // Track if music is playing
let isAssistantActive = false; // Track if the assistant is active
let conversationHistory = [];

let scheduleData = {
    event_name: '',
    description: '',
    start_date: '',
    end_date: ''
};
let taskName = '';
let weatherLocation = '';

let set_step = 0;
let add_hop = 0;
let remove_hop = 0;
let change_roll = 0;

// Set up speech recognition event handlers
recognition.onresult = async function(event) {
    const recognizedText = event.results[0][0].transcript.toLowerCase();
    console.log('Recognized Text:', recognizedText);

    if (isMusicPlaying && !isAssistantActive) {
        // Deactivate assistant when music is playing
        if (recognizedText.includes(assistantName.toLowerCase())) {
            console.log(`Assistant name "${assistantName}" detected. Activating assistant...`);
            pauseMusic(); // Pause music when the assistant is activated
            activateAssistant();
        }
        return; // Ignore other commands when the assistant is inactive
    }

    if (!isSpeaking) {
        if (recognizedText.includes(assistantName.toLowerCase())) {
            console.log(`Assistant name "${assistantName}" detected. Activating assistant...`);
            activateAssistant();
        } else if (isAssistantActive) {
            document.getElementById("userInputText").textContent = recognizedText;

            // Voice commands for music player
            if (recognizedText.includes("play song")) {
                deactivateAssistant();
                playMusic();
            } else if (recognizedText.includes("pause song")) {
                deactivateAssistant();
                pauseMusic();
            } else if (recognizedText.includes("stop song")) {
                deactivateAssistant();
                stopMusic();
            } else if (recognizedText.includes("next song")) {
                deactivateAssistant();
                nextMusic();
            } else if (recognizedText.includes("previous song")) {
                deactivateAssistant();
                prevMusic();
            } 
            // Existing assistant commands
            else if (set_step > 0 && set_step <= 10) {
                handleSetSchedule(recognizedText);
            } else if (add_hop > 0 && add_hop <= 2) {
                addTodoList(recognizedText);
            } else if (remove_hop > 0 && remove_hop <= 2) {
                removeTodoList(recognizedText);
            } else if (change_roll > 0 && change_roll <= 2) {
                handleWeatherLocation(recognizedText);
            } else if (recognizedText.includes("set schedule")) {
                set_step = 0;
                handleSetSchedule(recognizedText);
            } else if (recognizedText.includes("add to do list")) {
                add_hop = 0;
                addTodoList(recognizedText);
            } else if (recognizedText.includes("remove to do list") || recognizedText.includes("delete to do list")) {
                remove_hop = 0;
                removeTodoList(recognizedText);
            } else if (recognizedText.includes("change weather location")) {
                change_roll = 0;
                handleWeatherLocation(recognizedText);
            } else if (recognizedText.includes("current time") || recognizedText.includes("current date")) {
                handleDateTimeRequest();
            } else {
                const aiResponse = await getAIResponse(recognizedText, assistantName, assistantStyle, conversationHistory);
                displayAssistantResponse(aiResponse);
            }
        }
    }
};

// Music Player Controls
function playMusic() {
    console.log("Voice Command: Playing Music");
    const playButton = document.getElementById('play');
    if (playButton) playButton.click();
    isMusicPlaying = true;
    deactivateAssistant(); // Deactivate assistant when music starts
}

function pauseMusic() {
    console.log("Voice Command: Pausing Music");
    const pauseButton = document.getElementById('pause');
    if (pauseButton) pauseButton.click();
    isMusicPlaying = false;
}

function stopMusic() {
    console.log("Voice Command: Stopping Music");
    const stopButton = document.getElementById('stop');
    if (stopButton) stopButton.click();
    isMusicPlaying = false;
}

function nextMusic() {
    console.log("Voice Command: Next Song");
    const nextButton = document.getElementById('next');
    if (nextButton) nextButton.click();
}

function prevMusic() {
    console.log("Voice Command: Previous Song");
    const prevButton = document.getElementById('prev');
    if (prevButton) prevButton.click();
}

function startListening() {
    if (!isListening && !isSpeaking) {  // Only start listening if assistant is not speaking
        try {
            recognition.start();
            isListening = true;
        } catch (error) {
            console.error('Failed to start recognition:', error);
            setTimeout(startListening, 1000);  // Retry after a delay
        }
    }
}

function pauseRecognition() {
    if (isListening) {
        recognition.stop();
        isListening = false;
    }
}

function resumeRecognition() {
    if (!isSpeaking) {  // Resume only if not speaking
        setTimeout(startListening, 1000);  // Add a delay to avoid immediate restart
    }
}

recognition.onerror = function(event) {
    console.error("Speech recognition error:", event.error);
    if (['no-speech', 'network', 'audio-capture', 'not-allowed'].includes(event.error)) {
        if (isListening) {
            recognition.stop();
        }
        setTimeout(startListening, 1000);  // Restart after a delay on error
    }
};

recognition.onend = function() {
    console.log('Speech recognition ended.');
    isListening = false;
    if (!isSpeaking) {  // Only restart listening if assistant is not speaking
        setTimeout(startListening, 1000);  // Delay to ensure speech is fully done
    }
};

function handleSetSchedule(userResponse) { 
    // A sub-function to convert month names to numbers
    function convertMonthToNumber(monthName) {
        const months = {
            january: 1, february: 2, march: 3, april: 4, may: 5, june: 6,
            july: 7, august: 8, september: 9, october: 10, november: 11, december: 12
        };
        return months[monthName.toLowerCase()] || null;
    }

    // A sub-function to convert spoken time to 24-hour format
    function convertTimeTo24Hour(timeString) {
        const timeRegex = /(\d{1,2}):?(\d{2})?\s?(am|pm)?/i;
        const match = timeRegex.exec(timeString);
        if (!match) return null;

        let [_, hour, minute, period] = match;
        hour = parseInt(hour, 10);
        minute = minute || "00";

        if (period && period.toLowerCase() === "pm" && hour !== 12) hour += 12;
        if (period && period.toLowerCase() === "am" && hour === 12) hour = 0;

        return `${hour.toString().padStart(2, "0")}:${minute.padStart(2, "0")}`;
    }

    // A sub-function to convert ordinal days to numbers
    function convertDayToNumber(dayString) {
        const days = {
            first: 1, second: 2, third: 3, fourth: 4, fifth: 5, sixth: 6, seventh: 7,
            eighth: 8, ninth: 9, tenth: 10, eleventh: 11, twelfth: 12, thirteenth: 13,
            fourteenth: 14, fifteenth: 15, sixteenth: 16, seventeenth: 17, eighteenth: 18,
            nineteenth: 19, twentieth: 20, twentyfirst: 21, twentysecond: 22, twentythird: 23,
            twentyfourth: 24, twentyfifth: 25, twentysixth: 26, twentyseventh: 27,
            twentyeighth: 28, twentyninth: 29, thirtieth: 30, thirtyfirst: 31
        };
        return days[dayString.toLowerCase().replace(/\s+/g, '')] || parseInt(dayString, 10) || null; // Handle both text and numeric inputs
    }

    switch (set_step) {
        case 0:
            displayAssistantResponse("What is the name of the event?");
            set_step++;
            break;
        case 1:
            scheduleData.event_name = userResponse;
            displayAssistantResponse("What is the year for the start date?");
            set_step++;
            break;
        case 2:
            scheduleData.start_year = userResponse;
            displayAssistantResponse("What is the month for the start date?");
            set_step++;
            break;
        case 3:
            scheduleData.start_month = convertMonthToNumber(userResponse);
            if (!scheduleData.start_month) {
                displayAssistantResponse("I didn't understand the month. Please try again.");
            } else {
                displayAssistantResponse("What is the day of the start date?");
                set_step++;
            }
            break;
        case 4:
            scheduleData.start_day = convertDayToNumber(userResponse);
            if (!scheduleData.start_day) {
                displayAssistantResponse("I didn't understand the day. Please try again.");
            } else {
                displayAssistantResponse("What time does the event start? Please provide the time (e.g., 3:00 PM).");
                set_step++;
            }
            break;
        case 5:
            scheduleData.start_time = convertTimeTo24Hour(userResponse);
            if (!scheduleData.start_time) {
                displayAssistantResponse("I didn't understand the time. Please try again.");
            } else {
                // Construct full start date
                scheduleData.start_date = `${scheduleData.start_year}-${scheduleData.start_month}-${scheduleData.start_day} ${scheduleData.start_time}`;
                displayAssistantResponse("What is the year for the end date?");
                set_step++;
            }
            break;
        case 6:
            scheduleData.end_year = userResponse;
            displayAssistantResponse("What is the month for the end date?");
            set_step++;
            break;
        case 7:
            scheduleData.end_month = convertMonthToNumber(userResponse);
            if (!scheduleData.end_month) {
                displayAssistantResponse("I didn't understand the month. Please try again.");
            } else {
                displayAssistantResponse("What is the day of the end date?");
                set_step++;
            }
            break;
        case 8:
            scheduleData.end_day = convertDayToNumber(userResponse);
            if (!scheduleData.end_day) {
                displayAssistantResponse("I didn't understand the day. Please try again.");
            } else {
                displayAssistantResponse("What time does the event end? Please provide the time (e.g., 5:00 PM).");
                set_step++;
            }
            break;
        case 9:
            scheduleData.end_time = convertTimeTo24Hour(userResponse);
            if (!scheduleData.end_time) {
                displayAssistantResponse("I didn't understand the time. Please try again.");
            } else {
                // Construct full end date
                scheduleData.end_date = `${scheduleData.end_year}-${scheduleData.end_month}-${scheduleData.end_day} ${scheduleData.end_time}`;
                confirmSchedule();
                set_step++;
            }
            break;
        case 10:
            finalizeSchedule(userResponse);
            break;
        default:
            break;
    }
}

function confirmSchedule() {
    displayAssistantResponse(`I have the following details:
        Event: ${scheduleData.event_name}
        Start: ${scheduleData.start_date}
        End: ${scheduleData.end_date}
    Should I save this?`);
}

function finalizeSchedule(userResponse) {
    if (userResponse.toLowerCase() === 'yes') {
        // Send data to the PHP script via AJAX
        addSchedule(scheduleData);
    } else {
        displayAssistantResponse("Okay, the event was not added.");
        set_step = 0;
    }
}

function addSchedule(data) {
    // Use AJAX to send the schedule data to add_schedule.php
    fetch('../core/add_sched.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: new URLSearchParams(data)
    }).then(response => response.json())
    .then(result => {
        if (result.success) {
            displayAssistantResponse("The event has been successfully added.");
        } else {
            displayAssistantResponse("An error occurred while adding the event. Please try again.");
        }
    }).catch(error => {
        console.error('Error:', error);
    });
}

// Function to handle adding a to-do item
async function addTodoList(userResponse) {
    switch (add_hop) {
        case 0:
            displayAssistantResponse("What is the task you would like to add to your to-do list?");
            add_hop++;
            break;
        case 1:
            // Capture the task name from the user's response
            taskName = userResponse;
            // Confirm the addition of the task
            displayAssistantResponse(`You want to add "${taskName}" to your to-do list. Should I proceed?`);
            add_hop++;
            break;
        case 2:
            if (userResponse.toLowerCase() === 'yes') {
                // Proceed with adding the task
                await addTaskToDatabase(taskName);
            } else if (userResponse.toLowerCase() === 'no') {
                displayAssistantResponse("Okay, the task was not added.");
                add_hop = 0;
            }
            break;
        default:
            break;
    }
}

// Function to handle removing a to-do item
async function removeTodoList(userResponse) {
    switch (remove_hop) {
        case 0:
            displayAssistantResponse("What is the task you would like to remove from your to-do list?");
            remove_hop++;
            break;
        case 1:
            // Capture the task name from the user's response
            taskName = userResponse;
            // Confirm the removal of the task
            displayAssistantResponse(`You want to remove "${taskName}" from your to-do list. Should I proceed?`);
            remove_hop++;
            break;
        case 2:
            if (userResponse.toLowerCase() === 'yes') {
                // Proceed with removing the task
                await removeTaskFromDatabase(taskName);
            } else if (userResponse.toLowerCase() === 'no') {
                displayAssistantResponse("Okay, the task was not removed.");
                remove_hop = 0;
            }
            break;
        default:
            break;
    }
}

// Function to add task to the database
async function addTaskToDatabase(task) {
    try {
        const response = await fetch('../core/update_todo.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams({ task })
        });

        const result = await response.json();
        if (result.success) {
            displayAssistantResponse("The task has been added successfully!");
        } else {
            displayAssistantResponse("An error occurred while adding the task. Please try again.");
        }
    } catch (error) {
        console.error('Error adding task:', error);
        displayAssistantResponse("An error occurred while adding the task. Please try again.");
    }
}

// Function to remove task from the database
async function removeTaskFromDatabase(task) {
    try {
        const response = await fetch('../core/update_todo.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams({ task_name: task })
        });

        const result = await response.json();
        if (result.success) {
            displayAssistantResponse("The task has been removed successfully!");
        } else {
            displayAssistantResponse("An error occurred while removing the task. Please try again.");
        }
    } catch (error) {
        console.error('Error removing task:', error);
        displayAssistantResponse("An error occurred while removing the task. Please try again.");
    }
}

async function handleWeatherLocation(userResponse) {
    switch (change_roll) {
        case 0:
            displayAssistantResponse("What is the location you would like to change to your weather widget?");
            change_roll++;
            break;
        case 1:
            // Capture the weather location from the user's response
            weatherLocation = userResponse;
            // Confirm the change of weather location
            displayAssistantResponse(`You want to change your weather location to "${weatherLocation}". Should I proceed?`);
            change_roll++;
            break;
        case 2:
            if (userResponse.toLowerCase() === 'yes') {
                // Proceed with changing the location
                await changeWeatherLocation(weatherLocation);
            } else if (userResponse.toLowerCase() === 'no') {
                displayAssistantResponse("Okay, the weather location has not been changed.");
                change_roll = 0;
            }
            break;
        default:
            break;
    }
}

// Function to remove task from the database
async function changeWeatherLocation(location) {
    try {
        const response = await fetch('../core/change_weather.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams({ weather_location: location })
        });

        const result = await response.json();
        if (result.success) {
            displayAssistantResponse("The weather location has been changed successfully!");
        } else {
            displayAssistantResponse("An error occurred while changing the location of the weather. Please try again.");
        }
    } catch (error) {
        console.error('Error changing location:', error);
        displayAssistantResponse("An error occurred while changing the location of the weather widget. Please try again.");
    }
}

async function handleDateTimeRequest() {
    // Get the current date and time
    const currentDateTime = new Date();
    
    const dayNames = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
    const monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
    
    const dayOfWeek = dayNames[currentDateTime.getDay()];
    const month = monthNames[currentDateTime.getMonth()];
    const dayOfMonth = currentDateTime.getDate();
    const year = currentDateTime.getFullYear();
    
    const formattedDate = `${dayOfWeek}, ${month} ${dayOfMonth}, ${year}`;
    
    // Format the time as a readable string
    const hours = currentDateTime.getHours();
    const minutes = currentDateTime.getMinutes();
    const seconds = currentDateTime.getSeconds();
    
    // Convert to 12-hour format if needed
    const ampm = hours >= 12 ? 'PM' : 'AM';
    const formattedHours = hours % 12 || 12;

    const formattedMinutes = minutes < 10 ? '0' + minutes : minutes;
    const formattedSeconds = seconds < 10 ? '0' + seconds : seconds;

    // Construct the time string
    const formattedTime = `${formattedHours}:${formattedMinutes}:${formattedSeconds} ${ampm}`;

    displayAssistantResponse(`Today is ${formattedDate}, and the current time is ${formattedTime}.`);
}

// Handle assistant activation
function activateAssistant() {
    const assistantUI = document.getElementById("assistant-ui");
    assistantUI.classList.add("active");
    assistantUI.classList.remove("hidden");
    clearTimeout(idleTimer);
    isAssistantActive = true;

    if (isListening) {
        recognition.stop();
    }

    setTimeout(startListening, 500);  // Delay to allow for assistant's preparation
}

// Handle assistant deactivation
function deactivateAssistant() {
    const assistantUI = document.getElementById("assistant-ui");
    assistantUI.classList.remove("active");
    assistantUI.classList.add("hidden");
    isAssistantActive = false;
    recognition.stop();
}

async function unrealSpeechSpeak(text) {
    const API_BASE_URL = "https://api.v7.unrealspeech.com/stream";
    const API_KEY = "Bearer 7Yfzp1sQCgAYVzit3FezIFz5e2Ubn5clyEW3j1wp6I1IHb5iRd11Gr"; 
    const VOICE_ID = voiceAssistant || 'Will';

    try {
        // Pause speech recognition while TTS is playing
        pauseRecognition();
        isSpeaking = true;

        const response = await fetch(API_BASE_URL, {
            method: "POST",
            headers: {
                "Authorization": API_KEY,
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                Text: text,
                VoiceId: VOICE_ID,
                Bitrate: '192k',
                Speed: '0',
                Pitch: '1',
                Codec: 'libmp3lame'
            })
        });

        if (!response.ok) {
            throw new Error(`${response.status} ${response.statusText}\n${await response.text()}`);
        }

        const audioBlob = await response.blob();
        const audioUrl = URL.createObjectURL(audioBlob);

        // Play the TTS audio
        const audio = new Audio(audioUrl);
        audio.play();
        clearTimeout(idleTimer);

        // Resume speech recognition after TTS audio ends
        audio.onended = () => {
            isSpeaking = false;
            resumeRecognition();  // Restart recognition after speaking
        };
    } catch (error) {
        console.error('Error with Unreal Speech TTS:', error);
        isSpeaking = false;
        resumeRecognition();
    }

    // Set a timeout to deactivate assistant after 60 seconds of inactivity
    idleTimer = setTimeout(() => {
        deactivateAssistant();
    }, 60000);
}

// Display the assistant's response and handle TTS
async function displayAssistantResponse(responseText) {
    document.getElementById("assistant-ui").classList.remove("hidden");  // Ensure UI is visible
    const responseElement = document.getElementById("assistantResponse");
    responseElement.textContent = "";
    const speed = 75;

    // Call the Unreal Speech text-to-speech function
    await unrealSpeechSpeak(responseText);  // Await the TTS function to ensure audio plays before proceeding

    for (let i = 0; i < responseText.length; i++) {
        responseElement.textContent += responseText[i];
        await new Promise(resolve => setTimeout(resolve, speed));
    }
}

// Fetch AI response
async function getAIResponse(userInput, assistantName, assistantStyle, conversationHistory) {
    try {
        conversationHistory.push({ role: "user", content: userInput });

        const response = await fetch('https://api.mistral.ai/v1/chat/completions', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer VPuwjt8Dpc30wjpa0pPTdjOzWIvrSjCE',
            },
            body: JSON.stringify({
                model: "open-mistral-nemo",
                temperature: 0.7,
                max_tokens: 100,
                messages: [
                    { role: "system", content: `Your name is ${assistantName}. You are a ${assistantStyle}. The name of the user is ${userName}. Provide brief, clear, human responses without any emoticons.` },
                    ...conversationHistory
                ]
            })
        });

        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }

        const data = await response.json();
        let aiResponse = data.choices[0].message.content;

        conversationHistory.push({ role: "assistant", content: aiResponse });

        const wordLimit = 50;
        aiResponse = aiResponse.split(" ").slice(0, wordLimit).join(" ");

        return aiResponse;
    } catch (error) {
        console.error("Error getting AI response:", error);
        return "Sorry, I'm unable to process your request right now.";
    }
}
