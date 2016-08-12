<?php
include('session.php');
?>
<!DOCTYPE html>
<head>
    <link rel="stylesheet" href="css/style.css"/>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.0.0-alpha1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-csv/0.71/jquery.csv-0.71.min.js"></script>
    <script type="text/javascript">
    $(document).ready(function() {

    // The event listener for the file upload
    document.getElementById('txtFileUpload').addEventListener('change', upload, false);

    // Method that checks that the browser supports the HTML5 File API
    function browserSupportFileUpload() {
        var isCompatible = false;
        if (window.File && window.FileReader && window.FileList && window.Blob) {
        isCompatible = true;
        }
        return isCompatible;
    }

    // Method that reads and processes the selected file
    function upload(evt) {
        if (!browserSupportFileUpload()) {
            alert('The File APIs are not fully supported in this browser!');
            } else {
                var data = null;
                var file = evt.target.files[0];
                var reader = new FileReader();
                reader.readAsText(file);
                reader.onload = function(event) {
                    var csvData = event.target.result;
                    data = $.csv.toArrays(csvData);
                    console.log(csvData);
                    if (data && data.length > 0) {
                    alert('Imported -' + data.length + '- rows successfully!');

                    var form = document.createElement("form");
                    form.setAttribute("method", 'POST');
                    form.setAttribute("action", "/financereport/financereport/shipmentStatusToDelivered");
                    var dataString = '';
                    data.forEach(function(value){
                        dataString += value + ',';
                    })
                    dataString = dataString.substring(0,dataString.length-1);
                    var hiddenField = document.createElement("input");
                    hiddenField.setAttribute("type", "hidden");
                    hiddenField.setAttribute("name", "param");
                    hiddenField.setAttribute("value", dataString);
                    form.appendChild(hiddenField);

                    document.body.appendChild(form);
                    form.submit();
                    } else {
                        alert('No data to import!');
                    }
                };
                reader.onerror = function() {
                    alert('Unable to read ' + file.fileName);
                };
            }
        }
    });
</script>
<script>
    function validateForm() {
        var awb = document.forms["myForm"]["awbnumber"].value;
        if (awb == null || awb == "") {
            alert("Awb Number must be filled out");
            return false;
        }
    }
</script>
    </head>
    <body>
    <div class="grid Page-container">
        <div class="col-1-1">

            <div class="grid">
                <div class="col-2-12">
                    <div class="container" style="width: 247px;height: 58px;background-color: #e1e1e1;">
                        <div class="logo_h"></div>
                    </div>
                </div>

                <div class="col-10-12">
                    <div class="container">
                        <div class="page-breadcrumb">

                            <div class="page-heading">
                                <h1>Finance Report Dashboard</h1>
                                <div class="FRnavigation" style="align="right";"><a href="dashboard.php" ><b>Dashboard</b></a>
                             <a href="logout.php" ><b>Logout</b> </a>
                         </div>
                            </div>

                            <div class="clear"></div>
                        </div>


                    </div>
                </div>

            </div>
        </div>
    </div>

        <div style="height: 20px"></div>
        <div id="dvImportSegments" class="fileupload ">
        <fieldset>
            <legend>Upload your CSV File(Format: Shipment Id)</legend>
            <input type="file" name="File Upload" id="txtFileUpload" accept=".csv" />
        </fieldset>
    </div>
    </body>
</html>
