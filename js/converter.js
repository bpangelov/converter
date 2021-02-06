const API_URL = "http://localhost/converter/api/transformations";

const convert = () => {
    document.getElementById("converterOutput").value = "";

    const propertyCase = document.getElementById("propertyCase").value == 1 ? "snake" : "camel";
    const apiRequest = {
        "config": {
            "name":         "config name",
            "inputFormat":  document.getElementById("inputFormat").value,
            "outputFormat": document.getElementById("outputFormat").value,
            "tabulation":   document.getElementById("tabulation").value,
            "propertyCase": propertyCase,
        },
        "save":             false,
        "fileName":         "file name",
        "inputFileContent": document.getElementById("converterInput").value,
        "shareWith":        "",
    };

    fetch(API_URL, {
        method: "POST", 
        body: JSON.stringify(apiRequest),
        headers : { 
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(response => {
        document.getElementById("converterOutput").value = response.convertedFile;
    })
    .catch(err => {
        console.log('Fetch Error :', err);
    });
}

const populateHistory = () => {
    fetch(API_URL)
        .then(response => response.json())
        .then(data => {
            const historyItems = document.getElementById("historyItems");
            historyItems.innerHTML = '';

            data.historyEntries.forEach(entry => {
                const historyItem = document.createElement('a');
                historyItem.className = "dropdown-item";
                historyItem.innerHTML = entry.fileName;
                historyItem.onclick = () => getTransformation(entry.id);
                historyItems.appendChild(historyItem);
            });

            const sharedItems = document.getElementById("sharedItems");
            sharedItems.innerHTML = '';

            data.sharedEntries.forEach(entry => {
                const sharedItem = document.createElement('a');
                sharedItem.className = "dropdown-item";
                sharedItem.innerHTML = entry.fileName;
                sharedItem.onclick = () => getTransformation(entry.transformationID);
                sharedItems.appendChild(sharedItem);
            })
        }).catch(function(err) {
            console.log('Fetch Error :', err);
        });
}

const getTransformation = id => {
    fetch(API_URL + "/" + id)
        .then(response => response.json())
        .then(data => {
            document.getElementById("converterInput").value = data.originalFile;
            document.getElementById("converterOutput").value = data.convertedFile;
        }).catch(function(err) {
            console.log('Fetch Error :', err);
        });
}

document.getElementById('converterInput').addEventListener('keydown', function(e) {
    if (e.key == 'Tab') {
        e.preventDefault();
        var start = this.selectionStart;
        var end = this.selectionEnd;

        // set textarea value to: text before caret + tab + text after caret
        this.value = this.value.substring(0, start) +
            "\t" + this.value.substring(end);

        // put caret at right position again
        this.selectionStart =
        this.selectionEnd = start + 1;
    }
});

function onFileLoad(elementId, event) {
    document.getElementById(elementId).innerText = event.target.result;
}

function onChooseFile(event, onLoadFileHandler) {
    if (typeof window.FileReader !== 'function')
        throw ("The file API isn't supported on this browser.");
    let input = event.target;
    if (!input)
        throw ("The browser does not properly implement the event object");
    if (!input.files)
        throw ("This browser does not support the `files` property of the file input.");
    if (!input.files[0])
        return undefined;
    let file = input.files[0];
    let fr = new FileReader();
    fr.onload = onLoadFileHandler;
    fr.readAsText(file);
}

populateHistory();