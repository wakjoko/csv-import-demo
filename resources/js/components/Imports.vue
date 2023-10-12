<template>
    <div class="container-lg mt-5">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-end">
                    <input type="file" name="file" id="file" accept="text/csv" multiple @change="upload" />
                </div>
            </div>
        </div>
        <div class="card mt-5">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table caption-top" v-if="uploaded.length > 0">
                        <caption class="mb-3">List of Imports</caption>
                        <thead>
                          <tr>
                            <th scope="col">Time</th>
                            <th scope="col">File Name</th>
                            <th scope="col">Status</th>
                          </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(upload, index) in uploaded" :key="index">
                                <td>
                                    {{ luxon(upload.created_at).toFormat('f') }}<br />
                                    ({{ luxon(upload.created_at).toRelative( luxon() ) }})
                                </td>
                                <td>{{ upload.file_name }}</td>
                                <td>
                                    <span :id="`status${upload.id}`">
                                        {{ upload.status ?? 'pending' }}
                                    </span>
                                    <div class="progress" v-if="showProgress(upload.status)">
                                        <div :id="`progress${upload.id}`" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuemin="0" aria-valuemax="100"
                                            :aria-valuenow="upload.progress ?? 0"
                                            :style="{ width: `${upload.progress ?? 0}%` }">
                                            {{ upload.progress ?? 0 }}%
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <p v-else>Nothing imported yet..</p>
                </div>
            </div>
        </div>
    </div>
  </template>
  
  <script>
  import ImportService from "../services/ImportService";
  import { DateTime } from 'luxon';
  
  export default {
    name: "Imports",
    data() {
      return {
        uploaded: [],
      };
    },
    methods: {  
      upload(event) {
        let uploads = [];
        
        for (const file of event.target.files) {
          uploads.push(
            ImportService
              .set(file)
              .then((response) => {
                this.uploaded.push(response.data);
                this.updateProgress(response.data.id);
              })
          );
        }

        Promise.all(uploads).finally(() => event.target.value = null);
      },

      updateProgress(id) {
        const stream = new EventSource(`/api/imports/status/${id}`);
        const context = this;

        stream.onmessage = function (event) {
            const data = JSON.parse(event.data);

            if (Object.keys(data).length > 0) {
                const status = document.getElementById(`status${id}`);

                if (status.innerHTML !== data.status) {
                    status.innerHTML = data.status;
                }

                const progress = document.getElementById(`progress${id}`);

                progress.setAttribute("aria-valuenow", data.progress);
                progress.setAttribute("style", `width: ${data.progress}%`);
                progress.innerHTML = `${data.progress}%`;

                if (!context.showProgress(data.status)) {
                    stream.close();
                    progress.parentElement.remove();
                }
            }
        }
      },

      showProgress(status = 'pending') {
        return !['completed', 'failed'].includes(status);
      },

      luxon(date = null) {
        date = date ?? new Date().toISOString();
        return DateTime.fromISO(date);
      }
    },
    mounted() {
      ImportService.get().then((response) => {
        this.uploaded = response.data;

        this.uploaded
          .filter((uploaded) => this.showProgress(uploaded.status))
          .forEach((uploaded) => this.updateProgress(uploaded.id));
      });
    }
  };
  </script>