const messages = [
    'Cloning repository...',
    'Analyzing file structure...',
    'Extracting code metrics...',
    'Calculating complexity scores...',
    'Mapping commit history...',
    'Identifying hotspot files...',
    'Generating genome profile...',
    'Almost there...'
];

let messageIndex = 0;

document.getElementById('analyze-form').addEventListener('submit', function() {
    document.getElementById('loading-screen').classList.remove('hidden');
    document.getElementById('main-content').classList.add('hidden');

    setInterval(() => {
        messageIndex = (messageIndex + 1) % messages.length;
        document.getElementById('loading-status').textContent = messages[messageIndex];
    }, 3000);
});
