import http from "../http-common";

class ImportService {
    set(files) {
        let formData = new FormData();

        for (let i = 0; i < files.length; i++) {
            formData.append('files[]', files[i]);
        }

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