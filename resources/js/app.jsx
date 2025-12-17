import React, { useState } from 'react';
import ReactDOM from 'react-dom/client';
import Picker from 'emoji-picker-react';

function EmojiPickerExample() {

    const onEmojiClick = (event, emojiObject) => {
        console.log(emojiObject);
        const event1 = new CustomEvent('reactToBladeEvent', { detail: event });
        window.dispatchEvent(event1);
    };

    return (
        <Picker height={350} skinTonesDisabled={true} previewConfig={{ showPreview: false }}  onEmojiClick={onEmojiClick} />
    );
}

// React 18 style root rendering
const root = ReactDOM.createRoot(document.getElementById('customEmojiPicker'));
root.render(<EmojiPickerExample  />);
