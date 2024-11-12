<?php if ($modules['Assistant']['active']) { ?>
    <link rel="stylesheet" href="../modules/assistant_module/assistant.style.css">
    <!-- AI Assistant UI -->
    <div id="assistant-ui" class="hidden">
        <img id="microphone-icon" src="../assets/svg/microphone-svgrepo-com.svg" alt="Microphone">
        <p id="userInputText">Listening...</p>
        <p id="assistantResponse">Waiting for input...</p>
    </div>
<?php } ?>