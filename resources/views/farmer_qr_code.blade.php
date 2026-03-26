<!DOCTYPE html>

<html>
<head>
    <title>Farmer QR Code</title>
<style>

    body{
        font-family: Arial, sans-serif;
        background:#f4f6f9;
        display:flex;
        justify-content:center;
        align-items:center;
        height:100vh;
    }

    .card{
        background:white;
        width:350px;
        border-radius:10px;
        box-shadow:0 5px 20px rgba(0,0,0,0.15);
        padding:25px;
        text-align:center;
    }

    .title{
        font-size:22px;
        font-weight:bold;
        color:#2c3e50;
        margin-bottom:15px;
    }

    .info{
        font-size:15px;
        margin-bottom:6px;
        color:#555;
    }

    .qr-box{
        margin-top:15px;
    }

    .qr-box img{
        width:200px;
        border:1px solid #ddd;
        padding:10px;
        border-radius:6px;
    }

    .btn{
        display:inline-block;
        margin-top:15px;
        padding:10px 18px;
        background:#28a745;
        color:white;
        text-decoration:none;
        border-radius:5px;
        font-size:14px;
    }

    .btn:hover{
        background:#218838;
    }

</style>


</head>
<body>

<div class="card">


<div class="title">Farmer QR Code</div>

<div class="info"><strong>Name:</strong> {{ $farmer->name }}</div>
<div class="info"><strong>Phone:</strong> {{ $farmer->phone }}</div>

<div class="qr-box">
    <img src="{{ url('farmer_qr/farmer_'.$farmer->id.'.png') }}" alt="QR Code">
</div>

<a href="{{ url('farmer_qr/farmer_'.$farmer->id.'.png') }}" download class="btn">
    Download QR Code
</a>


</div>

</body>
</html>
