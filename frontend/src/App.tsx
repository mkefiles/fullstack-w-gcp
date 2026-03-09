import Home from "./pages/Home/Home.tsx";
import {Routes, Route} from "react-router-dom";
import KafkaForm from "./pages/KafkaForm/KafkaForm.tsx";

function App() {
    return (
        <Routes>
            <Route path="/" element={ <Home />} />
            <Route path="/kafka-form" element={ <KafkaForm />} />
        </Routes>
    )
}

export default App