window.onload = function(){ 
// Login
let username;
let password;

document.getElementById("myButton").onclick = function(){

    username = document.getElementById("myText").value;
    console.log(username);

    password = document.getElementById("myText1").value;
    console.log(password);

    document.getElementById("myText").value = null;
    document.getElementById("myText1").value = null;
};
};





// Navigation

function SecretImg(MyImage){
    window.location.href = "/Unit6 Template/pages/Secret.html";
}

function Home(Home){
    window.location.href = "/Unit6 Template/pages/index.html";
}

function Products(Products){
    window.location.href = "/Unit6 Template/pages/products.html";
}

function Fees(Fees){
    window.location.href = "/Unit6 Template/pages/fees.html";
}

function ContactUs(ContactUs){
    window.location.href = "/Unit6 Template/pages/contactus.html";
}

function Sign(Sign){
    window.location.href = "/Unit6 Template/pages/page2.html";
}


