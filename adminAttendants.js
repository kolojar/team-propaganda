//Make Row of user highlightable
for (const row of document.getElementsByClassName("clickHighlightRow")) {
    row.addEventListener("click", () => {
        if (row.classList.contains("trHighlight")) {
            row.classList.remove("trHighlight");
        }
        else {
            for (const row2 of document.getElementsByClassName("clickHighlightRow")) {
                row2.classList.remove("trHighlight");
            }
            row.classList.add("trHighlight");
        }
    });
}
//# sourceMappingURL=adminAttendants.js.map