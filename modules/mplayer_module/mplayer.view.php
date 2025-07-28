<?php if ($modules['Music Player']['active']) { ?>
    <link rel="stylesheet" href="../modules/mplayer_module/mplayer.style.css">
    <div id="music-player" class="col-md-6 d-flex flex-column align-items-start justify-content-end p-4 text-white">
        <div id="song-list">
            <ul id="songs" class="scrollable"></ul>
        </div>
        <div id="progress-container">
            <div id="progress-bar">
            </div>
        </div>
        <div id="controls">
            <button id="prev"><img src="../assets/svg/light-previous-999-svgrepo-com.svg" class="previous" alt="Previous"></button>
            <button id="play"><img src="../assets/svg/light-play-1003-svgrepo-com.svg" class="play" alt="Play"></button>
            <button id="pause"><img src="../assets/svg/light-pause-1006-svgrepo-com.svg" class="pause" alt="Pause"></button>
            <button id="stop"><img src="../assets/svg/light-stop-svgrepo-com.svg" class="stop" alt="Stop"></button>
            <button id="next"><img src="../assets/svg/light-next-998-svgrepo-com.svg" class="next" alt="Next"></button>
        </div>

    </div>
<?php } ?>
