<?php 
  print_r($_FILES);
  print_r($_POST);
?>

<!DOCTYPE html>
<html>
<head>
  <title></title>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>
<body>
  <form action="" method="post" id="uploadForm" enctype="multipart/form-data">  
    <input type="hidden" name="picture" value="uploadpic">  
<table width="100%" border="0" cellspacing="0" cellpadding="0">  
    <tr>  
            <td width="18%" background="<%=basePath%>tab/images/bg.gif" bgcolor="#FFFFFF"><div align="center"><span class="STYLE1">上传照片</span></div></td>  
           <td bgcolor="#FFFFFF">  
            <input type="file" id="pic" name="pic" />   
            <input type="button" value="上传" onclick="doUpload();">  
           </td>  
        </tr>  
</table>  
</form>  
<script type="text/javascript" src="http://apps.bdimg.com/libs/jquery/2.1.1/jquery.min.js"></script>
<script type="text/javascript">  
    function doUpload() {    
     var formData = new FormData($( "#uploadForm" )[0]);    
     $.ajax({    
          url: './index.php' ,  /*这是处理文件上传的servlet*/  
          type: 'POST',    
          data: formData,    
          async: false,    
          cache: false,    
          contentType: false,    
          processData: false,    
          success: function (returndata) {    
              document.getElementById("showpic").src="<%=basePath%>UploadImage?picture=showpic";/*这是预览图片用的，自己在文件上传表单外添加*/  
          },    
          error: function (returndata) {    
              alert(returndata);    
          }    
     });    
}    
</script>  
</body>
</html>