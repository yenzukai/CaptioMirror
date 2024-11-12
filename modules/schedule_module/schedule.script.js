let reminderQueue = [];
let Speaking = false;

window.addEventListener('load', function() {
    if (userPreferences.scheduler_active) {
        checkReminder();
        setInterval(() => checkReminder(), 2000);
    }
});

// Function to play the reminder one at a time
async function playReminder(reminder) {
    const eventTime = new Date(reminder.start_date).toLocaleString();
    const message = `Reminder for ${reminder.username}:\n` +
                    `Event: ${reminder.event_name}\n` +
                    `Description: ${reminder.description}\n` +
                    `Scheduled for: ${eventTime}`;

    console.log(message);

    // Use Unreal Speech to read out the reminder
    await unrealSpeak(message);

    // After the speech finishes, mark the reminder as notified
    await updateNotifyStatus(reminder.event_name);

    // Move to the next reminder in the queue
    Speaking = false;
    processQueue();  // Process the next item in the queue
}

// Function to process the queue one by one
function processQueue() {
    if (reminderQueue.length > 0 && !Speaking) {
        const nextReminder = reminderQueue.shift();  // Get the next reminder
        Speaking = true;  // Set flag to indicate TTS is speaking
        playReminder(nextReminder);  // Play the reminder
    }
}

// Function to check for reminders and add them to the queue
function checkReminder() {
    fetch('../core/check_reminder.php')
        .then(response => response.json())
        .then(data => {
            if (data.length > 0) {
                // Add new reminders to the queue
                data.forEach(reminder => {
                    // Check if reminder is already in the queue
                    if (!reminderQueue.some(r => r.event_name === reminder.event_name)) {
                        reminderQueue.push(reminder);
                    }
                });
                processQueue();
            }
        })
        .catch(error => console.error('Error fetching reminders:', error));
}

// Function to update the notify status in the database
async function updateNotifyStatus(eventName) {
    await fetch('../core/update_notify.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ event_name: eventName })
    });
}

// Function to speak text using Unreal Speech API
async function unrealSpeak(text) {
    const API_BASE_URL = "https://api.v7.unrealspeech.com/stream";
    const API_KEY = "Bearer 7Yfzp1sQCgAYVzit3FezIFz5e2Ubn5clyEW3j1wp6I1IHb5iRd11Gr"; 
    const VOICE_ID = userPreferences.assistant.voice_assistant || 'Will';

    try {
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

        // Create a new audio element
        const audio = new Audio(audioUrl);
       
        // Await until the audio has finished playing
        await new Promise((resolve) => {
            audio.addEventListener('ended', resolve);
            audio.play();
        });

    } catch (error) {
        console.error('Error with Unreal Speech TTS:', error);
    }
}
