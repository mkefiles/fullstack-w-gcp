import gcpLogo from "../../src/assets/logo--gcp.svg";
import dockerLogo from "../../src/assets/logo--docker.svg";
import kafkaLogo from "../../src/assets/logo--kafka.svg";
import phpLogo from "../../src/assets/logo--php.svg";
import reactLogo from "../../src/assets/logo--react.svg";
import typescriptLogo from "../../src/assets/logo--typescript.svg";

function Footer() {
    return (
        <footer>
            <div className="logo-container">
                <img src={ gcpLogo } />
                <img src={ dockerLogo } />
                <img src={ kafkaLogo } />
                <img src={ phpLogo } />
                <img src={ reactLogo } />
                <img src={ typescriptLogo } />
            </div>
        </footer>
    )
}

export default Footer;