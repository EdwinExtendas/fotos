<template>
    <div class="animated fadeIn" v-show="loaded">
        <div class="card">
            <div class="card-header">Dashboard</div>
            <div class="card-body">

                <lightbox
                        id="mylightbox"
                        :images="images"
                        :image_class=" 'col-sm-6 col-md-3' "
                        :album_class=" 'row' ">
                </lightbox>
            </div>
        </div>
    </div>
</template>

<script>
    import Lightbox from '../components/Lightbox';

    export default {
        name: 'dashboard',
        data () {
            return {
                loaded: false,
                images: []
            }
        },
        created: function() {
            this.fetchImages(0,40);
        },
        methods: {
            fetchImages: function (begin, end) {
                this.$http.post(
                    '/api/images',
                    JSON.stringify({from: begin, max: end})
                ).then(function (response) {
                        this.loaded = true;
                        this.images = JSON.parse(response.body);
                    }, (response) => {
                        console.log(response);
                    }
                );
            }
        },

        components: {
            Lightbox
        }
    }
</script>

<style>
    .card {
        max-width: 1160px;
        margin: 0 auto;
    }
</style>