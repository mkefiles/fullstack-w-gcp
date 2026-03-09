import {useEffect, useState} from "react";
import {useNavigate} from "react-router-dom";
import type {ApiEndpoint} from "../../../utils/common_types.ts";

function ApiResponse() {
    const [endpointResponse, setEndpointResponse] = useState<string>("Pending");
    const [isDisabled, setIsDisabled] = useState<boolean>(true);

    // NOTE: This is the end-point used to access the PHP Slim backend
    const endpoint : ApiEndpoint = {
        prod: "https://cr-api-backend-209033030686.us-east4.run.app/api/",
        dev: "http://localhost:80/api/"
    }

    const fetchResponse = async() => {
        try {
            // FIXME: Change URL for DEV / PROD accordingly
            const response = await fetch(endpoint.prod);

            if (response.ok === false) {
                setEndpointResponse("Bad Request");
                setIsDisabled(true);
            } else {
                setEndpointResponse(await response.text());
                setIsDisabled(false);
            }
        } catch (err) {
            setEndpointResponse(`Error: ${err}`);
            setIsDisabled(true);
        }
    }

    useEffect(() => {
        fetchResponse();
    }, []);

    const navigate = useNavigate();

    return (
        <>
            <div className="api-check">
                <span>:: API Connection: { endpointResponse } ::</span>
            </div>
            <hr/>
            <div className="center">
                <button
                    onClick={() => navigate("/kafka-form")}
                    disabled={ isDisabled }>Next</button>
            </div>
        </>
    )
}

export default ApiResponse;