function updateCounts(text) {
    //text = removeInvisibleChars(text);
    // Convert non-breaking spaces to normal space
    text = text.replace(/\u00A0/g, ' ');
    if (text.endsWith('\n')) text = text.slice(0, -1);

    const $msg = document.querySelector(".message-wrapper .message-block");
    const $spans = $msg
        .closest(".form-group")
        .querySelectorAll(".message-limit span");

    if (text.includes("(Online Booking Link)")) {
        text = text.replace(/\(Online Booking Link\)/g, bookingLink);
    }
    if (text.includes("(Clinic Name)")) {
        text = text.replace(/\(Clinic Name\)/g, clinicName);
    }
    if (text.includes("(Clinic Phone Number)")) {
        text = text.replace(/\(Clinic Phone Number\)/g, clinicPhoneNumber);
    }
    text = text.replace(/\(([^)]+)\)/g, "$1");

    const segmentedMessage = new SegmentedMessage(text);
    
    if ($spans.length >= 2) {
        $spans[0].textContent =
            segmentedMessage.encodingName == "GSM-7"
                ? segmentedMessage.numberOfCharacters
                : segmentedMessage.messageSize / 16;
        $spans[1].textContent = segmentedMessage.numberOfCharacters
            ? segmentedMessage.segmentsCount
            : 0;
    }
}
function removeInvisibleChars(str) {
    return str.replace(/[\u200B-\u200D\uFEFF]/g, '');
}
