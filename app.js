const express = require("express");
const ejs = require("ejs");

const app = express();

const PORT_NUMBER = 8000;


app.use(express.static("node_modules/bootstrap/dist/css"));
app.use(express.static("node_modules/bootstrap/dist/js"));

// Serving static files
app.use(express.static('styles'));
app.use(express.json())
app.use(express.urlencoded({ extended: true })); // Middleware for forms
app.engine("html", ejs.renderFile); // Set view engine to ejs
app.set("view engine", "html"); // search views folder for html files

app.listen(PORT_NUMBER, function () {
    console.log(`listening on port ${PORT_NUMBER}`);
});

app.get("/", function(req, res){
    res.render("index.html");
});

app.get("/backlog", function(req, res){
    res.render("product_backlog.html");
});

app.get("/board", function(req, res){
    res.render("sprint_board.html");
});

app.get("/", function(req, res){
    res.render("index.html");
});