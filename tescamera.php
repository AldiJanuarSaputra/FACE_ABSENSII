<!DOCTYPE html>
<html>
<body style="text-align:center">
<h2>Test Kamera</h2>
<video id="video" width="500" autoplay playsinline></video>

<script>
navigator.mediaDevices.getUserMedia({video:true})
.then(stream=>{
 document.getElementById("video").srcObject = stream;
})
.catch(err=>{
 alert("Error: " + err);
 console.log(err);
});
</script>
</body>
</html>