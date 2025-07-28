window.addEventListener('load', function () {
    const playButton = document.getElementById('play');
    const pauseButton = document.getElementById('pause');
    const stopButton = document.getElementById('stop');
    const nextButton = document.getElementById('next');
    const prevButton = document.getElementById('prev');
    const songList = document.getElementById('songs');
    const progressBar = document.getElementById('progress-bar');

    let audio = new Audio();
    let currentSongIndex = 0;
    let playlist = [];

    // Fetch songs from the server
    function fetchSongs() {
        fetch('../core/fetch_songs.php')
            .then(response => response.json())
            .then(songs => {
                playlist = songs;
                updateSongList();
            })
            .catch(error => console.error('Error fetching songs:', error));
    }

    function updateSongList() {
        songList.innerHTML = '';
        const startIndex = Math.max(0, currentSongIndex - 1);
        const endIndex = Math.min(playlist.length, currentSongIndex + 2);

        playlist.slice(startIndex, endIndex).forEach((song, index) => {
            const li = document.createElement('li');
            li.textContent = song.name;
            li.className = index + startIndex === currentSongIndex ? 'active' : '';
            li.addEventListener('click', () => playSong(index + startIndex));
            songList.appendChild(li);
        });

        // Scroll the list to keep the current song centered
        songList.scrollTop = currentSongIndex > 0 ? (currentSongIndex - 1) * 50 : 0; // Adjust based on item height
    }

    function playSong(index) {
        if (index < 0 || index >= playlist.length) return;

        currentSongIndex = index;
        audio.src = playlist[index].path;
        audio.play();
        updateSongList();
    }

    function play() {
        if (!audio.src) {
            playSong(currentSongIndex);
        } else {
            audio.play();
        }
    }

    function pause() {
        audio.pause();
    }

    function stop() {
        audio.pause();
        audio.currentTime = 0;
    }

    function next() {
        playSong((currentSongIndex + 1) % playlist.length);
    }

    function prev() {
        playSong((currentSongIndex - 1 + playlist.length) % playlist.length);
    }

    function updateProgress() {
        const progress = (audio.currentTime / audio.duration) * 100 || 0;
        progressBar.style.width = `${progress}%`;
    }

    // Autoplay the next song when the current song ends
    audio.addEventListener('ended', next);

    // Update progress bar and dot
    audio.addEventListener('timeupdate', updateProgress);

    // Event listeners
    playButton.addEventListener('click', play);
    pauseButton.addEventListener('click', pause);
    stopButton.addEventListener('click', stop);
    nextButton.addEventListener('click', next);
    prevButton.addEventListener('click', prev);

    fetchSongs(); // Initialize playlist
});
