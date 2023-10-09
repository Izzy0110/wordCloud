<?php

include('dbConfig.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <style>
        h1 {
            color: #0F2C59;
            text-align: center;
            font-size: 50px;
        }

        body {
            text-align: center;
            background-color: #F8F0E5;

        }
        
        .container {
            display: flex;
            flex-direction: column; /* Ensure elements stack vertically */
            align-items: center;
            justify-content: center;
            padding-top: 30px;
            color: #0F2C59;
        }

        .word-cloud-box {
            width: 100%;
            height: 100%;
            background-color: #DAC0A3;
            display: none;
            justify-content: center;
            align-items: center;
            border-radius: 10px;
            margin: 0 auto; /* Center horizontally */
            margin-top: 20px;
        }

        /* Style the word cloud image within the box */
        .word-cloud {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .word-cloud-box h2 {
            align-items: center;
            text-align: center; /* Center-align the text */
            margin-top: 10px; /* Add some top margin for spacing */
        }
        
        .btn {
            padding: 10px;
            font-size: 15px;
            color: #0F2C59;
            background: #DAC0A3;
            border: none;
            border-radius: 5px;
            margin-top: 10px;
        }

        .input {
            width: 303px;
            padding: 12px;
            margin: 8px 0;
            display: inline-block;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        .table-container {
        display: inline-block;
        align-items: center; /* Make the container inline-block */
        text-align: center; /* Left-align the table within the container */
        }

        .table {
            font-family: Arial, Helvetica, sans-serif;
            border-collapse: collapse;
            width: 100%;
        }

        .table td, .table th {
            border: 1px solid #ddd;
            padding: 8px;
        }

        .table tr:nth-child(even){
            background-color: #f2f2f2;
        }

        .table tr:hover {
            background-color: #ddd;
        }

        .table th {
            padding-top: 12px;
            padding-bottom: 12px;
            text-align: left;
            background-color: #04AA6D;
            color: white;
        }

        .pagination {
            display: inline-block;
            margin-top: 10px;
        }

        .pagination a {
            color: black;
            float: left;
            padding: 8px 16px;
            text-decoration: none;
        }

        .pagination a.active {
            background-color: #4CAF50;
            color: white;
            border-radius: 5px;
        }

        .pagination a:hover:not(.active) {
            background-color: #ddd;
            border-radius: 5px;
        }

        #tableData {
            margin: 20px auto;
            padding: 20px;
            border-radius: 5px;
        }

        select {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        

    </style>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Word Cloud</title>
    <!-- Include AnyChart library -->
    <script src="https://cdn.anychart.com/releases/8.10.0/js/anychart-bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

</head>
<body>
    <h1>WORD CLOUD</h1>

    <div class="container">
        <form id="uploadForm" method="post" enctype="multipart/form-data">

        <label for="file">Select a CSV file:</label><br>
        <input type="file" id="file" name="file" class="btn" accept=".csv"><br><br>

        <label for="name">CHART TITLE</label><br>
        <input type="text" id="name" name="name" class="input" placeholder="Enter title name..."><br><br>

        <button type="submit" id="submit" class="btn">Submit</button>
        </form>
    </div>

    <div id="tableData" class="table-container"></div>

    <script>
    $(document).ready(function() {
        // Function to submit the form data via AJAX
        function submitForm() {
            var formData = new FormData($('#uploadForm')[0]);

            $.ajax({
                url: "upload.php",
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    // Display the response in the #tableData div
                    $("#tableData").html(response);

                    $("#tableData table").DataTable();
                }
            });
        }

        // Event listener for form submission
        $("#uploadForm").submit(function(event) {
            event.preventDefault();
            submitForm();
        });
    });
    
    </script>

    <br><div id="container" class="word-cloud-box" style="width: 100%; height: 500px;"></div>

    <script>

        function showWordCloudContainer() {
            var wordCloudContainer = document.querySelector('.word-cloud-box');
            wordCloudContainer.style.display = 'block';
            console.log(wordCloudContainer.style.display);
        }

        $(document).on('click', '#Generate', function(){
            event.preventDefault();
            var Column = $('#column').val();
            var Table = $('#hiddentable').val();

            anychart.onDocumentReady(function () {
                // Make an AJAX request to fetch data from the server-side script
                $.ajax({
                    url: 'fetch_data.php', // Replace with the actual URL of your PHP script
                    type: 'POST',
                    data: {
                        'column': Column,
                        'table': Table
                    },
                    success: function (data) {
                        //Create a tag cloud chart with the dynamic data
                        var chart = anychart.tagCloud(data);

                        // Set chart configuration
                        chart.title(Table);
                        chart.angles([0]);
                        chart.colorRange(true);
                        chart.colorRange().length('80%');

                        // // Format tooltips
                        var formatter = "{%value}{scale:(1)(1000)(1000)(1000)|()( thousand)( million)( billion)}";
                        var tooltip = chart.tooltip();
                        tooltip.format(formatter);

                        // // Display the chart in the container
                        chart.container("container");
                        chart.draw();
                        showWordCloudContainer();
                    },
                    error: function (error) {
                        console.error('Error fetching data:', error);
                    }
                });
            });
        })
        
    </script>
    
</body>
</html>
