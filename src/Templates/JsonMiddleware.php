<!DOCTYPE html>
<html>
    <title><?php ?></title>
    <style>
        textarea {
            width: 100%;
            box-sizing: border-box;
        }

    </style>
<body>

<h3>Response status</h3>
<p><?php echo htmlspecialchars($responseStatusMessage);?></p>
<h3>Response headers</h3> <textarea cols="100" rows="5"><?php echo htmlspecialchars(json_encode($responseHeaders, JSON_PRETTY_PRINT));?></textarea>
<h3>Response body</h3>
<textarea cols="100" rows="5"><?php echo htmlspecialchars(json_encode(json_decode($responseBody), JSON_PRETTY_PRINT));?></textarea>
<h3>SQL Queries</h3>
<textarea cols="100" rows="5"><?php echo htmlspecialchars(json_encode($sqlQueries, JSON_PRETTY_PRINT));?></textarea>
<h3>Request url</h3> <p><?php echo htmlspecialchars($requestUri);?></p>
<h3>Request headers</h3> <textarea cols="100" rows="5"><?php echo htmlspecialchars(json_encode($requestHeaders, JSON_PRETTY_PRINT));?></textarea>
<h3>Request body</h3>
<textarea cols="100" rows="5"><?php echo htmlspecialchars(json_encode(json_decode($requestBody), JSON_PRETTY_PRINT));?></textarea>
</body>
</html>