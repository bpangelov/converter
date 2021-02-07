const API_URL = "http://localhost/converter/api/";
const TRANSFORMATIONS_URL = API_URL + "transformations";
const CONFIGS_URL = API_URL + "configs";

const convert = () => {
    const propCaseOption = document.getElementById("propertyCase").value
    const propertyCase = propCaseOption == 1 ? "none" : propCaseOption == 2 ? "snake" : "camel";
    const apiRequest = {
        "config": {
            "name":         document.getElementById("configName").value,
            "inputFormat":  document.getElementById("inputFormat").value,
            "outputFormat": document.getElementById("outputFormat").value,
            "tabulation":   document.getElementById("tabulation").value,
            "propertyCase": propertyCase,
        },
        "save":             document.getElementById('saveCheck').checked,
        "fileName":         document.getElementById("transformationName").value,
        "inputFileContent": document.getElementById("converterInput").value,
        "shareWith":        "",
    };

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
        populateHistory();
    })
    .catch(err => {
        console.log('Fetch Error :', err);
    });
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
                const historyItem = document.createElement('a');
                historyItem.className = "dropdown-item";
                historyItem.innerHTML = entry.fileName;
                historyItem.onclick = () => getTransformation(entry.id);
                historyItems.appendChild(historyItem);

                const cnf = document.createElement('a');
                cnf.className = "dropdown-item";
                cnf.innerHTML = entry.configName;
                cnf.onclick = () => getConfig(entry.configName);
                configs.appendChild(cnf);
            });

            const sharedItems = document.getElementById("sharedItems");
            sharedItems.innerHTML = '';
            const sharedConfigs = document.getElementById("sharedConfigs");
            sharedConfigs.innerHTML = '';
            data.sharedEntries.forEach(entry => {
                const sharedItem = document.createElement('a');
                sharedItem.className = "dropdown-item";
                sharedItem.innerHTML = entry.fileName;
                sharedItem.onclick = () => getTransformation(entry.transformationID);
                sharedItems.appendChild(sharedItem);

                const cnf = document.createElement('a');
                cnf.className = "dropdown-item";
                cnf.innerHTML = entry.configName;
                cnf.onclick = () => getConfig(entry.configName);
                sharedConfigs.appendChild(cnf);
            })
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

populateHistory();