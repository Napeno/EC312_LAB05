

var WEBSITE = "https://www.muchmade.id.vn";
var CK = "ck_eecf7bf41059cfc863199c798aa6cc531f6520fc"; // Get it from WooCommerce -> Settings -> Advanced Settings -> Rest API
var CS = "cs_c92f287761ba6c1cdc8997673f752abde3763f1d"; // Get it from WooCommerce -> Settings -> Advanced Settings -> Rest API

var ACTIVITY_FOLDER_ID = "1ZVos6xsJUkev58hy3AHK6KKgxfC5OSN4FXQBn9nq-IY"; // Where you want to upload the activity spreadsheets.
var TITLES = ["First Name", "Last Name", "Email", "Status", "Notes", "Quantity", "Total"]; // Title of the spreadsheets.
/**
 * Method that fires when the webapp receives a GET request
 */
function doGet(e) {
  syncOrders();
  return HtmlService.createHtmlOutput("Request received");
}

/**
 * Method that fires when the webapp receives a POST request
 */
function doPost(e) {
  syncOrders();
  return HtmlService.createHtmlOutput("Post request received");
}

/**
 * Trigger function for starting the sync process to check new orders from WooCommerce.
 */
function syncOrders() {
    var sheetName = SpreadsheetApp.getActiveSpreadsheet().getActiveSheet().getName();
    fetchOrders(sheetName)
}

function fetchOrders(sheetName) {
    var yesterdayDate = new Date(Date.now() - 864e5).toISOString();
    var url = WEBSITE + "/wp-json/wc/v2/orders?consumer_key=" + CK + "&consumer_secret=" + CS + "&after=" + yesterdayDate + "&per_page=100";
    var options = {
        "method": "GET",
        "Content-Type": "application/x-www-form-urlencoded;charset=UTF-8",
        "muteHttpExceptions": true
    };
    var result = UrlFetchApp.fetch(url, options);
    var orderList = {};
    if (result.getResponseCode() == 200) {
        orderList = JSON.parse(result.getContentText());
    }
    for (var i = 0; i < orderList.length; i++) {
        var orderDataRow = [];
        orderDataRow.push(orderList[i]["billing"]["first_name"]);
        orderDataRow.push(orderList[i]["billing"]["last_name"]);
        orderDataRow.push(orderList[i]["billing"]["email"]);
        orderDataRow.push(orderList[i]["status"]);
        orderDataRow.push(orderList[i]["customer_note"]);
        var itemsList = orderList[i]["line_items"];
        var totalOrderPrice = 0; // Tổng tiền của đơn hàng
        var items = "";
        for (var k = 0; k < itemsList.length; k++) {
            var itemName = itemsList[k]["name"];
            var quantity = itemsList[k]["quantity"];
            var itemTotal = parseFloat(itemsList[k]["total"]);
            totalOrderPrice += itemTotal; // Cộng giá tiền của mỗi mặt hàng vào tổng đơn hàng
            items += quantity + " x " + itemName + "\n";
            orderDataRow.push(quantity);
            orderDataRow.push(itemTotal);
            var doc = SpreadsheetApp.openById(getCreateDocumentID(itemName)).getActiveSheet();
            doc.appendRow(orderDataRow);
            removeDuplicates(doc);
        }
        orderDataRow.push(items);
        orderDataRow.push(totalOrderPrice); // Thêm tổng tiền vào hàng dữ liệu đơn hàng
        var doc = SpreadsheetApp.getActiveSpreadsheet();
        var generalDocument = doc.getSheetByName(sheetName);
        generalDocument.appendRow(orderDataRow);
        removeDuplicates(generalDocument);
    }
}


/**
 * Method that removes duplicates from a sheet received by argument.
 */
function removeDuplicates(sheet) { // SpreadsheetApp.spreadsheet.sheet
    var data = sheet.getDataRange().getValues();
    var newData = [];

    for (i in data) {
        var row = data[i];
        var duplicate = false;
        for (j in newData) {
            if (row.join() == newData[j].join()) {
                duplicate = true;
            }
        }
        if (!duplicate) {
            newData.push(row);
        }
    }

    sheet.clearContents();
    sheet.getRange(1, 1, newData.length, newData[0].length).setValues(newData);
}

/**
 * Method that checks if the documentName exists in the drive. If it does, return it.
 * If not, create it in the activities´ folder, initialize it and return it.
 */
function getCreateDocumentID(docName) {
    var docID;
    var files = DriveApp.searchFiles('title = "' + docName + '"');

    // If the file does not exist, it creates one in the folder of activities and
    // includes the first row with the titles.
    if (!files.hasNext()) {
        var folder = DriveApp.getFolderById(ACTIVITY_FOLDER_ID);
        var spreadSheet = SpreadsheetApp.create(docName);
        doc = DriveApp.getFileById(spreadSheet.getId());
        DriveApp.getRootFolder().removeFile(doc);

        folder.addFile(doc);
        docID = DriveApp.searchFiles('title = "' + docName + '"').next().getId();
        var doc = SpreadsheetApp.openById(docID).getActiveSheet();
        doc.appendRow(TITLES);
        
        // Customizing row
        doc.getRange(1, 1, 1, 7).setBackground("#4a86e8");
        doc.getRange(1, 1, 1, 7).setFontColor("white");
        doc.getRange(1, 1, 1, 7).setFontWeight("bold");
    } else { // If it exists, return it, ONLY ONE POSSIBLE!
        docID = files.next().getId();
    }

    return docID;
}
