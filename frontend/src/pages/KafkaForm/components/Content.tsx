import {useEffect, useState} from "react";
import {validateString} from "../../../utils/utilities.ts";
import type {ApiEndpoint} from "../../../utils/common_types.ts";

function Content() {
    const [kafkaMessage, setKafkaMessage] = useState("");
    const [submitIsDisabled, setSubmitIsDisabled] = useState<boolean>(true);
    const [kafbatIsDisabled, setKafbatIsDisabled] = useState<boolean>(true);
    const [endpointResponse, setEndpointResponse] = useState<string>("Pending Submission");

    const payload = {
        message: kafkaMessage
    }

    // NOTE: This is the end-point used to access the PHP Slim backend
    const endpoint : ApiEndpoint = {
        prod: "https://cr-api-backend-209033030686.us-east4.run.app/api/producer",
        dev: "http://localhost:80/api/producer"
    }

    function handleInputChange(event : React.ChangeEvent<HTMLTextAreaElement>) {
        // DESC: Validate input before updating useState
        if (validateString(event.target.value) === true) {
            // DESC: If valid, enable the button and set the `kafkaMessage`
            setKafkaMessage(event.target.value);
            setSubmitIsDisabled(false);
        } else {
            setSubmitIsDisabled(true);
        }
    }

    function handleFormSubmit(e : React.SubmitEvent<HTMLFormElement>) {
        e.preventDefault();

        // DESC: Send `kafkaMessage` to Kafka
        fetchResponse();

        // DESC: Clear text-area and disable submit button
        e.target.reset();
        setSubmitIsDisabled(true);
    }

    const fetchResponse = async () => {

        try {
            // FIXME: Change URL for DEV / PROD accordingly
            const response = await fetch(endpoint.prod, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify(payload)
            });

            if (!response.ok) {
                setEndpointResponse("Bad Network Response");
            }

            setEndpointResponse(await response.text());
        } catch (err) {
            setEndpointResponse(`Error -- ${err}`);
        }
    }

    useEffect(() => {
        // DESC: If successful, enable button to review Kafbat
        if (endpointResponse.includes("Successful")){
            setKafbatIsDisabled(false);
        }
    }, [endpointResponse]);

    return (
        <main>
            <h1>Kafka Producer</h1>
            <hr/>
            <form onSubmit={handleFormSubmit}>
                <textarea
                    placeholder="Message Criteria: Length 10 - 140 characters; Only alpha-numeric and typical punctuation characters."
                    onChange={handleInputChange}>
                </textarea>
                <input type="submit" value="Submit" disabled={submitIsDisabled} />
            </form>
            <hr/>
            <div className="api-check">
                <span>:: Message Send Status: {endpointResponse} ::</span>
            </div>
            <hr/>
            <div className="center">
                <button
                    onClick={() => { window.location.href = "https://cr-kafka-ui-209033030686.us-east4.run.app/ui/clusters/kafbat-cluster/all-topics/frontend-messages/" }}
                    disabled={kafbatIsDisabled}>Review Kafbat UI</button>
            </div>

        </main>
    )
}

export default Content;