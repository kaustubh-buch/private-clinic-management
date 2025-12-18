const emojiBtn = document.getElementById('emoji-btn');
const emojiPicker = document.getElementById('customEmojiPicker');
const emojiGrid = document.getElementById('emojiGrid');
const messageBlock = document.querySelector('.message-block');

function insertEmoji(emoji, elementId) {
    const input = document.getElementById(elementId);

    // Get cursor position
    const start = input.selectionStart;
    const end = input.selectionEnd;
    const text = input.value;

    // Insert emoji at cursor
    input.value = text.slice(0, start) + emoji + text.slice(end);

    // Move cursor after inserted emoji
    const newPos = start + emoji.length;
    input.setSelectionRange(newPos, newPos);

    // Focus the input again
    input.focus();
}

function togglePicker(show) {
    if (show) {
        const rect = emojiBtn.getBoundingClientRect();
        emojiPicker.style.top = `${rect.bottom + window.scrollY - 400}px`;
        emojiPicker.style.left = `${rect.left + window.scrollX - 10}px`;
        emojiPicker.style.display = 'block';
    } else {
        emojiPicker.style.display = 'none';
    }
}

emojiBtn.addEventListener('click', e => {
    e.preventDefault();
    const isVisible = emojiPicker.style.display === 'block';
    const activeElement = document.activeElement;
    console.log('Focused element:', activeElement);
    togglePicker(!isVisible);
});


document.addEventListener('click', e => {
    if (!emojiPicker.contains(e.target) && !emojiBtn.contains(e.target)) {
        togglePicker(false);
    }
},true);


window.addEventListener('resize', () => {
    const isVisible = emojiPicker.style.display === 'block';
    if (isVisible) {
        togglePicker(true);
    }
});
