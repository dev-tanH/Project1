$(document).ready(function () {
  $(".btn").click(function (e) {
    e.preventDefault();

    var email = $("#email").val();
    var rating = $(".feedbackValue:checked").val();
    var feedback = $(".description_area").val();
    validateFields(email, rating, feedback);
    token = getAuthToken(email);
    response = postFeedback(token, email, rating, feedback);
    if (response == false) {
      alert("Some error occured!");
      throw "Some error occured!";
    }
    alert("Feedback sent!");
    location.reload();
  });
});

function validateFields(email, rating, feedback) {
  if (email.length == 0) {
    alert("email is required!!");
    throw "email is required!!";
  }
  if (typeof rating == "undefined") {
    alert("Please select a rating");
    throw "Please select a rating";
  }
  if (feedback.length == 0) {
    alert("Message cannot be empty");
    throw "Message cannot be empty";
  }

  if (!validateEmail(email)) {
    alert("Incorrect email format!");
    throw "Incorrect email format!";
  }
}

function validateEmail(email) {
  var reg = /^([\w-\.]+@+([\w-]+\.)+[\w-]{2,4})?$/;
  return reg.test(email);
}

function getAuthToken(email) {
  token = "";
  $.ajax({
    method: "GET",
    url: "http://0.0.0.0:8080/getToken/" + email,
    async: false,
  }).done(function (data) {
    data = JSON.parse(data);
    if (!data["access token"]) {
      alert("Some error occured!");
      throw "Some error occured!";
    }
    token = data["access token"];
  });
  return token;
}

function postFeedback(token, email, rating, feedback) {
  $.ajax({
    method: "POST",
    url: "http://0.0.0.0:8080/feedbacks?bearer=" + token,
    data: {
      email: email,
      rating: rating,
      description: feedback,
    },
    async: false,
  }).done(function (data) {
    data = JSON.parse(data);
    response = data["success"];
  });
  return response;
}
