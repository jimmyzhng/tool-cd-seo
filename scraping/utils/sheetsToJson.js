// use this in Google Sheets Script Editor to convert a list in Google Sheets to a JSON format
function generateJSON() {
  var sheet = SpreadsheetApp.getActiveSpreadsheet().getActiveSheet();
  var data = sheet.getDataRange().getValues();

  var jsonArray = [];

  // assuming data starts at second row (skips header)
  for (var i = 1; i < data.length; i++) {
    var row = data[i];
    jsonArray.push({
      position: row[0],
      domain: row[1]
    });
  }

  var jsonString = JSON.stringify(jsonArray, null, 2);  // null and 2 to format JSON

  // Saves to your Google Drive
  saveToDrive(jsonString);
}

function saveToDrive(jsonString) {
  var filename = 'Top1000Websites.json';

  var blob = Utilities.newBlob(jsonString, "application/json", filename);
  DriveApp.createFile(blob);
}