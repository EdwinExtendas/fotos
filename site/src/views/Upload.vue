<template>
    <div class="card">
        <div class="card-header">Upload </div>
        <div class="card-body">
            <vue-dropzone ref="myVueDropzone" id="dropzone" :options="dropzoneOptions" v-on:vdropzone-success="uploadSuccess"></vue-dropzone>
        </div>


    </div>
</template>

<script>
    import vue2Dropzone from 'vue2-dropzone'
    import 'vue2-dropzone/dist/vue2Dropzone.min.css'
    import Album from '../components/Album'
    import Config from '../../api-config'

    export default {
        name: 'upload',
        data () {
            return {
                dropzoneOptions: {
                    url: Config.base_api_url + '/api/image/upload',
                    thumbnailWidth: 180,
                    withCredentials: true
                },
                uploaded: []
            }
        },
        methods: {
            uploadSuccess: function (file, response) {
                this.uploaded.push(response);
            }
        },
        components: {
            vueDropzone: vue2Dropzone, Album
        }
    }
</script>

<style>
    .vue-fine-uploader-gallery-files {
        max-height: 100%;
    }
    .vue-fine-uploader-gallery-nodrop-container, .vue-fine-uploader-gallery-dropzone {
        max-height: 100%;
    }

</style>