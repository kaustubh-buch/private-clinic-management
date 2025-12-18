const Embed = Quill.import('blots/embed');
const Delta = Quill.import('delta');
let allowedPaste;

class PlaceholderBlot extends Embed {
    static create(value) {
        const node = super.create();
        node.setAttribute('data-text', value.text);
        node.innerText = value.text;
        node.style.color = value.color || '#465DFF';
        return node;
    }

    static value(node) {
        return {
            text: node.getAttribute('data-text'),
            color: node.style.color || '#465DFF'
        };
    }
}

PlaceholderBlot.blotName = 'placeholder';
PlaceholderBlot.tagName = 'span';
PlaceholderBlot.className = 'placeholder-insert';

Quill.register(PlaceholderBlot);


let quill;

function initializeQuill(selector, templateMessage, placeholder) {
    quill = new Quill(selector, {
        modules: {
            toolbar: false,
        },
        clipboard: {
            matchVisual: false
        },
        formats: ['color', 'placeholder'],
        placeholder: placeholder,
        theme: 'snow',
    });
    updateCounts(getFullTextIncludingEmbeds());

    quill.on('text-change',() => {
        const fullText = getFullTextIncludingEmbeds();
        updateCounts(fullText);
        const $hiddenMessage = $('#hiddenMessage');
        $hiddenMessage.val(fullText);
        if ($hiddenMessage.hasClass('error-message')) {
            $hiddenMessage.valid();
        }
    });

    quill.keyboard.addBinding({ key: ' ' }, {
        collapsed: true
    }, function(range, context) {
        const [leaf] = quill.getLeaf(range.index - 1);
        if (leaf && leaf.statics) {
            quill.insertText(range.index, ' ');
            quill.setSelection(range.index + 1);
            return false; // prevent default
        }
        return true;
    });

    // Paste handling
    allowedPaste = true;
    // console.log(templateMessage);
    handlePasteEvent();
    quill.clipboard.dangerouslyPasteHTML(0, templateMessage);
    allowedPaste = false;
}

function handlePasteEvent() {
    quill.clipboard.addMatcher(Node.ELEMENT_NODE, (node, delta) => {
        if (allowedPaste) {
            return delta;
        }
        return new Delta().insert(node.innerText);
    });
    
    quill.clipboard.addMatcher(Node.TEXT_NODE, function(node, delta) {
        const text = node.data.replace(/ /g, '\u00A0'); // replace spaces with nbsp
        return new Delta().insert(text);
    });
}

function getFullTextIncludingEmbeds() {
    const delta = quill.getContents();
    let result = '';

    delta.ops.forEach(op => {
        if (typeof op.insert === 'string') {
            result += op.insert;
        } else if (op.insert.placeholder) {
            result += op.insert.placeholder.text ;

        }
    });

    return result;
}

function addLink(text) {
    text = '(' + text + ')';

    var index = quill.getSelection()?.index || 0;
    quill.insertEmbed(index, 'placeholder', { text });
    quill.insertText(index + 1, ' ');
    quill.setSelection(index + 2);
}

function addOptOutMessage(text) {
    const index = quill.getSelection()?.index || 0;
    const color = '#747985';

    quill.insertEmbed(index, 'placeholder', {
        text: decodeHtmlEntities(text),
        color
    });
    quill.setSelection(index + 1);

}

function setAllowedPaste(value){
    allowedPaste = value;
}

function addEmoji(data){
    const range = quill.getSelection(true);

    if (range) {
        // Insert emoji at cursor
        quill.insertText(range.index, data.emoji);

        // Optionally move cursor after emoji
        quill.setSelection(range.index + data.emoji.length);
    }
}

 