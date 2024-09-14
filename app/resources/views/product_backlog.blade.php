@extends('layouts.app')

<body>
    <div id = "sidebar" class = "flex flex-column h-100 py-3 bg-dark text-white" style = "height: 100vh; width: 15vw; font-size: 1.5rem;">
        <div class = "mb-5">
            <h1 class = "px-3"><a class = "nav-link" href = "/">Mangie</a></h1>
        </div>
        <ul class = "nav nav-pills flex flex-column mb-auto">
            <li><a class = "nav-link link-light" href = "/backlog">Product backlog</a></li>
            <li><a class = "nav-link link-light" href = "/board">Sprint Board</a></li>
        </ul>
    </div>
    <!-- adding a row at the top of the website -->
    <div class = "d-flex flex-column h-100" style = "width: 85vw;">
        <div class = "d-flex justify-content-between px-3 py-3 bg-secondary">
            <h1>Sprint Board</h1>
            <h1>PFP</h1>
        </div>
    </div>
        
    <!-- <h1>We are going to make this cool cool thing guys</h1> -->
    
</body>
</html>