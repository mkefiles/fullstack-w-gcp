// import {useNavigate} from "react-router-dom";
import ApiResponse from "./ApiResponse.tsx";

function Content() {
    // const navigate = useNavigate();
    return (
        <main>
            <h1>Kafka Producer</h1>
            <hr/>
            <p>
                This is an example of a Kafka Producer. The following screen provides the end-user with the ability
                to <i>publish</i> to a running Kafka instance.
            </p>
            <p>
                The technology used is as follows:
            </p>
            <ul>
                <li>PHP</li>
                <ul>
                    <li>PHP Slim for the API</li>
                    <li>PHP-DI for Dependency Injection</li>
                    <li>php-rdkafka for the Kafka Client</li>
                </ul>
                <li>TypeScript</li>
                <ul>
                    <li>React for the frontend UI</li>
                </ul>
                <li>Apache Kafka</li>
                <li>Kafbat UI</li>
                <ul>
                    <li>To <i>consume</i> the message in a UI</li>
                </ul>
                <li>Docker</li>
                <li>GCP</li>
            </ul>
            <p>
                Please click <i>Next</i> to proceed.
            </p>
            <hr/>
            <ApiResponse />
        </main>
    )
}

export default Content;