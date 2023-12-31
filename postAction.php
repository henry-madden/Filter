<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
<?php 
// Start session 
session_start(); 
 
// Include and initialize DB class 
require_once 'DB.class.php'; 
$db = new DB(); 
 
// File upload path 
$uploadDir = "uploads/images/"; 
 
// Allow file formats 
$allowTypes = array('jpg','png','jpeg','gif'); 
 
// Set default redirect url 
$redirectURL = 'index.php'; 
 
$statusMsg = $errorMsg = ''; 
$sessData = array(); 
$statusType = 'danger'; 
if(isset($_POST['imgSubmit'])){ 
     
     // Set redirect url 
    $redirectURL = 'addEdit.php'; 
 
    // Get submitted data 
    $title    = $_POST['title']; 
    $id        = $_POST['id']; 
     
    // Submitted user data 
    $galData = array( 
        'title'  => $title 
    ); 
     
    // Store submitted data into session 
    $sessData['postData'] = $galData; 
    $sessData['postData']['id'] = $id; 
     
    // ID query string 
    $idStr = !empty($id)?'?id='.$id:''; 
     
    if(empty($title)){ 
        $error = '<br/>Enter the gallery title.'; 
    } 
     
    if(!empty($error)){ 
        $statusMsg = 'Please fill all the mandatory fields.'.$error; 
    }else{ 
        if(!empty($id)){ 
            // Update data 
            $condition = array('id' => $id); 
            $update = $db->update($galData, $condition); 
            $galleryID = $id; 
        }else{ 
            // Insert data 
            $insert = $db->insert($galData); 
            $galleryID = $insert; 
        } 
         
        $fileImages = array_filter($_FILES['images']['name']); 
        if(!empty($galleryID)){ 
            if(!empty($fileImages)){ 
                foreach($fileImages as $key=>$val){ 
                    // File upload path 
                    $fileName = $galleryID.'_'.basename($fileImages[$key]); 
                    $targetFilePath = $uploadDir . $fileName; 
                     
                    // Check whether file type is valid 
                    $fileType = pathinfo($targetFilePath,PATHINFO_EXTENSION); 
                    if(in_array($fileType, $allowTypes)){ 
                        // Upload file to server 
                        if(move_uploaded_file($_FILES["images"]["tmp_name"][$key], $targetFilePath)){ 
                            // Image db insert 
                            $imgData = array( 
                                'gallery_id' => $galleryID, 
                                'file_name' => $fileName 
                            ); 
                            $insert = $db->insertImage($imgData); 
                        }else{ 
                            $errorUpload .= $fileImages[$key].' | '; 
                        } 
                    }else{ 
                        $errorUploadType .= $fileImages[$key].' | '; 
                    } 
                } 
             
                $errorUpload = !empty($errorUpload)?'Upload Error: '.trim($errorUpload, ' | '):''; 
                $errorUploadType = !empty($errorUploadType)?'File Type Error: '.trim($errorUploadType, ' | '):''; 
                $errorMsg = !empty($errorUpload)?'<br/>'.$errorUpload.'<br/>'.$errorUploadType:'<br/>'.$errorUploadType; 
            } 
             
            $statusType = 'success'; 
            $statusMsg = 'Gallery images has been uploaded successfully.'.$errorMsg; 
            $sessData['postData'] = ''; 
             
            $redirectURL = 'index.php'; 
        }else{ 
            $statusMsg = 'Some problem occurred, please try again.'; 
            // Set redirect url 
            $redirectURL .= $idStr;     
        } 
    } 
     
    // Status message 
    $sessData['status']['type'] = $statusType; 
    $sessData['status']['msg']  = $statusMsg; 
}elseif(($_REQUEST['action_type'] == 'block') && !empty($_GET['id'])){ 
    // Update data 
    $galData = array('status' => 0); 
    $condition = array('id' => $_GET['id']); 
    $update = $db->update($galData, $condition); 
    if($update){ 
        $statusType = 'success'; 
        $statusMsg  = 'Gallery data has been blocked successfully.'; 
    }else{ 
        $statusMsg  = 'Some problem occurred, please try again.'; 
    } 
     
    // Status message 
    $sessData['status']['type'] = $statusType; 
    $sessData['status']['msg']  = $statusMsg; 
}elseif(($_REQUEST['action_type'] == 'unblock') && !empty($_GET['id'])){ 
    // Update data 
    $galData = array('status' => 1); 
    $condition = array('id' => $_GET['id']); 
    $update = $db->update($galData, $condition); 
    if($update){ 
        $statusType = 'success'; 
        $statusMsg  = 'Gallery data has been activated successfully.'; 
    }else{ 
        $statusMsg  = 'Some problem occurred, please try again.'; 
    } 
     
    // Status message 
    $sessData['status']['type'] = $statusType; 
    $sessData['status']['msg']  = $statusMsg; 
}elseif(($_REQUEST['action_type'] == 'delete') && !empty($_GET['id'])){ 
    // Previous image files 
    $conditions['where'] = array( 
        'id' => $_GET['id'], 
    ); 
    $conditions['return_type'] = 'single'; 
    $prevData = $db->getRows($conditions); 
                 
    // Delete gallery data 
    $condition = array('id' => $_GET['id']); 
    $delete = $db->delete($condition); 
    if($delete){ 
        // Delete images data 
        $condition = array('gallery_id' => $_GET['id']); 
        $delete = $db->deleteImage($condition); 
         
        // Remove files from server 
        if(!empty($prevData['images'])){ 
            foreach($prevData['images'] as $img){ 
                @unlink($uploadDir.$img['file_name']); 
            } 
        } 
         
        $statusType = 'success'; 
        $statusMsg  = 'Gallery has been deleted successfully.'; 
    }else{ 
        $statusMsg  = 'Some problem occurred, please try again.'; 
    } 
     
    // Status message 
    $sessData['status']['type'] = $statusType; 
    $sessData['status']['msg']  = $statusMsg; 
}elseif(($_POST['action_type'] == 'img_delete') && !empty($_POST['id'])){ 
    // Previous image data 
    $prevData = $db->getImgRow($_POST['id']); 
                 
    // Delete gallery data 
    $condition = array('id' => $_POST['id']); 
    $delete = $db->deleteImage($condition); 
    if($delete){ 
        @unlink($uploadDir.$prevData['file_name']); 
        $status = 'ok'; 
    }else{ 
        $status  = 'err'; 
    } 
    echo $status;die; 
} 
 
// Store status into the session 
$_SESSION['sessData'] = $sessData; 
     
// Redirect the user 
header("Location: ".$redirectURL); 
exit(); 
?>