<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
<?php 
// Start session 
session_start(); 
 
$postData = $galData = array(); 
 
// Get session data 
$sessData = !empty($_SESSION['sessData'])?$_SESSION['sessData']:''; 
 
// Get status message from session 
if(!empty($sessData['status']['msg'])){ 
    $statusMsg = $sessData['status']['msg']; 
    $statusMsgType = $sessData['status']['type']; 
    unset($_SESSION['sessData']['status']); 
} 
 
// Get posted data from session 
if(!empty($sessData['postData'])){ 
    $postData = $sessData['postData']; 
    unset($_SESSION['sessData']['postData']); 
} 
 
// Get gallery data 
if(!empty($_GET['id'])){ 
    // Include and initialize DB class 
    require_once 'DB.class.php'; 
    $db = new DB(); 
     
    $conditions['where'] = array( 
        'id' => $_GET['id'], 
    ); 
    $conditions['return_type'] = 'single'; 
    $galData = $db->getRows($conditions); 
} 
 
// Pre-filled data 
$galData = !empty($postData)?$postData:$galData; 
 
// Define action 
$actionLabel = !empty($_GET['id'])?'Edit':'Add'; 
?>

<div class="container">
    <h1><?php echo $actionLabel; ?> Gallery</h1>
    <hr>
    
    <!-- Display status message -->
    <?php if(!empty($statusMsg)){ ?>
    <div class="col-xs-12">
        <div class="alert alert-<?php echo $statusMsgType; ?>"><?php echo $statusMsg; ?></div>
    </div>
    <?php } ?>
    
    <div class="row">
        <div class="col-md-6">
            <form method="post" action="postAction.php" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Images:</label>
                    <input type="file" name="images[]" class="form-control" multiple>
                    <?php if(!empty($galData['images'])){ ?>
                        <div class="gallery-img">
                        <?php foreach($galData['images'] as $imgRow){ ?>
                            <div class="img-box" id="imgb_<?php echo $imgRow['id']; ?>">
                                <img src="uploads/images/<?php echo $imgRow['file_name']; ?>">
                                <a href="javascript:void(0);" class="badge badge-danger" onclick="deleteImage('<?php echo $imgRow['id']; ?>')">delete</a>
                            </div>
                        <?php } ?>
                        </div>
                    <?php } ?>
                </div>
                <div class="form-group">
                    <label>Title:</label>
                    <input type="text" name="title" class="form-control" placeholder="Enter title" value="<?php echo !empty($galData['title'])?$galData['title']:''; ?>" >
                </div>
                <a href="index.php" class="btn btn-secondary">Back</a>
                <input type="hidden" name="id" value="<?php echo !empty($galData['id'])?$galData['id']:''; ?>">
                <input type="submit" name="imgSubmit" class="btn btn-success" value="SUBMIT">
            </form>
        </div>
    </div>
</div>
