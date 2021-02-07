const API_URL = "http://localhost/converter/api/";
const TRANSFORMATIONS_URL = API_URL + "transformations";
const CONFIGS_URL = API_URL + "configs";
const SHARES_URL = API_URL + "share";

let transformationToShare = null;

const transformationsApiRequest = () => {
    const propCaseOption = document.getElementById("propertyCase").value
    return {
        "config": {
            "name":         document.getElementById("configName").value,
            "inputFormat":  document.getElementById("inputFormat").value,
            "outputFormat": document.getElementById("outputFormat").value,
            "tabulation":   document.getElementById("tabulation").value,
            "propertyCase": getPropertyCaseFromIndex(propCaseOption),
        },
        "save":             document.getElementById('saveCheckbox').checked,
        "fileName":         document.getElementById("transformationName").value,
        "inputFileContent": document.getElementById("converterInput").value,
        "shareWith":        "",
    };
}

const convert = () => {
    const apiRequest = transformationsApiRequest();
    fetch(TRANSFORMATIONS_URL, {
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
        transformationToShare = response.id;
        populateHistory();
    })
    .catch(err => {
        console.log('Fetch Error :', err);
    });
}

const update = () => {
    const apiRequest = transformationsApiRequest();
    fetch(TRANSFORMATIONS_URL, {
        method: "PUT", 
        body: JSON.stringify(apiRequest),
        headers : { 
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(response => {
        document.getElementById("converterOutput").value = response.convertedFile;
        transformationToShare = response.id;
        populateHistory();
    })
    .catch(err => {
        console.log('Fetch Error :', err);
    });
}

const share = () => {
    if (!transformationToShare) {
        return;
    }

    const request = {
        "transformationID": transformationToShare,
        "userID": document.getElementById("usernameShare").value
    };
    fetch(SHARES_URL, {
        method: "POST", 
        body: JSON.stringify(request),
        headers : { 
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        console.log(response);
    })
    .catch(err => {
        console.log('Fetch Error :', err);
    });
}

function getHistoryEntry(name, getHandler, removeHanler, allowDelete) {
    const historyItem = document.createElement('div');
    historyItem.className = "dropdown-item";

    const getLink = document.createElement('a');
    getLink.innerHTML = name;
    getLink.onclick = getHandler;
    historyItem.appendChild(getLink);

    if (allowDelete) {
        const removeLink = document.createElement('span');
        removeLink.className = "badge";
        removeLink.innerHTML = "премахване";
        removeLink.style.color = "red";
        removeLink.onclick = removeHanler;
        historyItem.appendChild(removeLink);
    }

    return historyItem;
}

const populateHistory = () => {
    fetch(TRANSFORMATIONS_URL)
        .then(response => response.json())
        .then(data => {
            const historyItems = document.getElementById("historyItems");
            historyItems.innerHTML = '';
            const configs = document.getElementById("configs");
            configs.innerHTML = '';
            data.historyEntries.forEach(entry => {
                const historyItem =  getHistoryEntry(entry.fileName, () => getTransformation(entry.id), 
                    () => removeTransformation(entry.id), true);
                historyItems.appendChild(historyItem);
            });

            data.historyConfigs.forEach(entry => {
                const cnf =  getHistoryEntry(entry.name, () => getConfig(entry.name), 
                    () => removeConfig(entry.name), true);
                configs.appendChild(cnf);
            });

            const sharedItems = document.getElementById("sharedItems");
            sharedItems.innerHTML = '';
            const sharedConfigs = document.getElementById("sharedConfigs");
            sharedConfigs.innerHTML = '';
            data.sharedEntries.forEach(entry => {
                const sharedItem = getHistoryEntry(entry.fileName, () => getTransformation(entry.transformationID), 
                    () => removeTransformation(entry.transformationID), false);
                sharedItems.appendChild(sharedItem);
            });

            data.sharedConfigs.forEach(entry => {
                const cnf =  getHistoryEntry(entry.name, () => getConfig(entry.name), 
                    () => removeConfig(entry.name), false);
                sharedConfigs.appendChild(cnf);
            });
        }).catch(function(err) {
            console.log('Fetch Error :', err);
        });
}

const getConfig = name => {
    fetch(CONFIGS_URL + "/" + name)
        .then(response => response.json())
        .then(config => {
            assignConfig(config)
        }).catch(function(err) {
            console.log('Fetch Error :', err);
        });
}

const getTransformation = id => {
    fetch(TRANSFORMATIONS_URL + "/" + id)
        .then(response => response.json())
        .then(data => {
            assignConfig(data.config)
            document.getElementById("converterInput").value = data.originalFile;
            document.getElementById("converterOutput").value = data.convertedFile;
            document.getElementById("transformationName").value = data.fileName;
            transformationToShare = id;
        }).catch(function(err) {
            console.log('Fetch Error :', err);
        });
}

const textBoxes = ["converterInput", "converterOutput"];
textBoxes.forEach(textBox => {
    document.getElementById(textBox).addEventListener("keydown", function(e) {
        // Tab should work normally inside the text areas.
        if (e.key == "Tab") {
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
});

function removeConfig(name) {
    fetch(CONFIGS_URL + "/" + name, {
        method: "DELETE", 
    })
    .then(response => {
        console.log('Item deleted');
        console.log(response);
        populateHistory();
    })
    .catch(function(err) {
        console.log('Fetch Error :-S', err);
    });
}

function removeTransformation(id) {
    fetch(TRANSFORMATIONS_URL + "/" + id, {
        method: "DELETE", 
    })
    .then(response => {
        console.log('Item deleted');
        console.log(response);
        populateHistory();
    })
    .catch(function(err) {
        console.log('Fetch Error :-S', err);
    });
}

function onFileLoad(elementId, event) {
    document.getElementById(elementId).value = event.target.result;
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

function assignConfig(config) {
    document.getElementById("configName").value = config.name
    document.getElementById("inputFormat").value = config.inputFormat;
    document.getElementById("outputFormat").value = config.outputFormat;
    document.getElementById("tabulation").value = config.tabulation;
    document.getElementById("propertyCase").value = getPropertyCaseIndex(config.propertyCase);
}

function getPropertyCaseIndex(propCase) {
    switch (propCase) {
        case 'none':
            return 1;
        case 'snake':
            return 2;
        case 'camel':
            return 3;
        default:
            throw ("Unknown format");
    }
}

function getPropertyCaseFromIndex(index) {
    switch(index) {
        case '1':
            return "none";
        case '2':
            return "snake"; 
        case '3':
            return "camel";
        default:
            throw ("Unknown format");
    }
}

function saveOutputAsFile() {
    const textToWrite = document.getElementById('converterOutput').value;
    const textFileAsBlob = new Blob([ textToWrite ], { type: 'text/plain' });
    const outputFormat = document.getElementById("outputFormat").value;
    const fileNameToSaveAs = "output." + outputFormat;
  
    var downloadLink = document.createElement("a");
    downloadLink.download = fileNameToSaveAs;
    downloadLink.innerHTML = "Download File";
    if (window.webkitURL != null) {
      // Chrome allows the link to be clicked without actually adding it to the DOM.
      downloadLink.href = window.webkitURL.createObjectURL(textFileAsBlob);
    } else {
      // Firefox requires the link to be added to the DOM before it can be clicked.
      downloadLink.href = window.URL.createObjectURL(textFileAsBlob);
      downloadLink.onclick = destroyClickedElement;
      downloadLink.style.display = "none";
      document.body.appendChild(downloadLink);
    }
  
    downloadLink.click();
}

function destroyClickedElement(event) {
    // remove the link from the DOM
    document.body.removeChild(event.target);
}

var button = document.getElementById('download-btn');
button.addEventListener('click', saveOutputAsFile);

const onSaveCheckboxClick = () => {
    const checkbox = document.getElementById("saveCheckbox");
    const saveInfo = document.getElementById("saveInfo");
    if (checkbox.checked) {
        saveInfo.style.display = "block";
    } else {
        saveInfo.style.display = "none";
    }
};

populateHistory();