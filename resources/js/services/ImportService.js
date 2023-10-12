import http from "../http-common";

class ImportService {
    set(file) {
        let formData = new FormData();

        formData.append('file', file);

        return http.post("/imports", formData, {
            headers: {
                "Content-Type": "multipart/form-data"
            }
        });
    }

    get() {
        return http.get("/imports");
    }
}

export default new ImportService();