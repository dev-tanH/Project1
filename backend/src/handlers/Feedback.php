<?php

namespace Api\Handlers;

use Phalcon\Di\Injectable;
use Phalcon\Http\Response;

class Feedback extends Injectable
{
    /**
     * submitFeedback method get the feedback data from the post request,
     * performs the validations and saves it to database if its valid
     */
    public function submitFeedback()
    {
        $feedback = $this->request->getPost();
        //validating feedback data
        $response = $this->validateFeedback($feedback);
        if ($response['success'] != true) {
            $this->response->setStatusCode(400, "Missing data");
            return json_encode($response);
        }
        //santizing (clearing) feedback data to prevent malicious data from being saved in database
        $santizedFeedback = $this->sanitizeFeedback($feedback);
        //saving data to database
        $response = $this->saveFeedback($santizedFeedback);
        $this->response->setStatusCode(201, "CREATED");
        return json_encode($response);
    }

    /**
     * getFeedbacks method fetch all the feedbacks from the database to return to the request
     */
    public function getFeedbacks()
    {
        //fetching feedbacks from the database
        $response = $this->fetchAllFeedbacks();
        $this->response->setStatusCode(200, "OK");
        return json_encode($response);
    }

    /**
     * validateFeedback method varifies if the payload (feedback content) is valid i.e. contains all the required fields
     * and no required fieldsare empty
     * It takes array as parameter and returns success if the feedback content is valid
     * and returns false along with the corresponding error if the feedback content is invalid
     */
    public function validateFeedback($feedback)
    {
        if (!isset($feedback['rating']) || !isset($feedback['description']) || !isset($feedback['email'])) {
            return ['success' => false, 'message' => 'All fields are required.'];
        }
        if (empty($feedback['rating']) || empty($feedback['description']) || empty($feedback['email'])) {
            return ['success' => false, 'message' => 'Fields cannot be empty.'];
        }
        return ['success' => true];
    }

    /**
     * santizedFeedback method sanitises the feedback content(data)
     * It takes feedback array as param and returns the santized array of that data
     */
    public function sanitizeFeedback($feedback)
    {
        $secure = new \Api\Handlers\Secure();
        $santizedFeedback = $secure->sanitizeArray($feedback);
        return $santizedFeedback;
    }

    /**
     * saveFeedback method saves the feedback content(data) in the database
     * and returns the corresponding message
     * @param [array] $feedback
     */
    public function saveFeedback($feedback)
    {
        $response = $this->mongo->feedbacks->insertOne($feedback);
        if ($response->getInsertedCount() < 1) {
            return ['success' => false, 'message' => 'Feedback could not be saved for some reasons. Please try again after sometime'];
        }
        return ['success' => true, 'message' => 'Feedback saved successfully'];
    }

    /**
     * fetchAllFeedbacks method fetches all the feedbacks from the database
     */
    public function fetchAllFeedbacks()
    {
        $response = $this->mongo->feedbacks->find()->toArray();
        if (count($response) < 1) {
            return ['success' => true, 'message' => 'No feedback found.'];
        }
        return $response;
    }
}
