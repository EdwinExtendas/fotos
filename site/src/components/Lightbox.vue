<template>
    <div>
        <div class="my-gallery" :class="album_class">
            <div :class="image_class" v-for="image,key in images">
                <a class="lightbox" :href="image.filename|resizeUrl" :data-caption="image.filename|rawUrl">
                    <img height="180" :src="image.filename|thumbUrl" alt="" :title="image.title"/>
                </a>
            </div>
            <div v-bind:class="{ 'loader': loading }"></div>
        </div>
        <link rel="stylesheet" type="text/css" href="/static/css/baguetteBox.min.css">
    </div>
</template>

<script>
    import Config from '../../api-config'

    export default {
        props: {
            id: {
                type: String,
                required: true
            },
            images: {
                type: Array,
                required: true
            },
            image_class: {
                type: String
            },
            album_class: {
                type: String
            },
            album_id: {
                type: Number,
                required: false
            },
            options: {
                type: Object,
                required: false
            }
        },
        data() {
            return {
                baguette: null,
                loading: false,
                done: false,
                from: 40,
                max: 20,
            }
        },
        watch: {
            images: function (value) {
                this.runLightbox();
            }
        },
        mounted() {
            try {
                window.$ = window.jQuery = require('jquery');
                this.baguette = require('../../static/js/baguetteBox.min');
                this.baguette.run('.my-gallery');
            } catch (e) {
            }

            window.onscroll = () => {
                let bottomOfWindow = document.documentElement.scrollTop + window.innerHeight >= (document.documentElement.offsetHeight);
                if (bottomOfWindow) {
                    this.fetchImages(this.from, this.max);
                }
            };
        },
        filters: {
            rawUrl: function (value) {
                return "<a target='_blank' href='"+Config.base_api_url+'/images/raw/'+value+"'>rawrr :3</a>";
            },
            resizeUrl: function (value) {
                return Config.base_api_url+'/images/resize/'+value;
            },
            thumbUrl: function (value) {
                return Config.base_api_url+'/images/thumb/'+value;
            }
        },
        methods: {
            fetchImages: function (begin, end) {
                if(!this.done) {
                    this.loading = true;

                    let route = '/api/images';
                    if(this.album_id === 0) {
                        route = route+'/unlinked';
                    }

                    this.$http.post(
                        route,
                        JSON.stringify({from: begin, max: end, album: this.album_id})
                    ).then(function (response) {
                            this.loading = false;
                            let data = JSON.parse(response.body);
                            if (data.length === 0) {
                                this.done = true;
                                return;
                            }
                            data.forEach(function (e) {
                                this.images.push(e);
                            }, this);
                            this.from += this.max;
                            this.runLightbox();

                        }, (response) => {
                            console.log(response);
                        }
                    );
                }

            },
            runLightbox: function () {
                let self = this;
                setTimeout(function () {
                    self.baguette.run('.my-gallery');
                }, 500);
            }

        },
        components: {
        }
    }
</script>

<style>
    .selected {
        border: 3px solid lime;
    }
    .full-image a {
        color: #FFFFFF;
    }

    .my-gallery {
        padding: 20px;
    }

    .my-gallery .lightbox img:hover {
        transform: scale(1.05) !important;
        box-shadow: 0 8px 15px rgba(0,0,0,0.3);
    }

    .my-gallery img {
        width: 100%;
        margin-bottom: 30px;
        transition: 0.2s ease-in-out;
        box-shadow: 0 2px 3px rgba(0,0,0,0.2);
        border-radius: 4px;
    }

    .loader {
        border: 8px solid #f3f3f3; /* Light grey */
        border-top: 8px solid #3498db; /* Blue */
        border-radius: 50%;
        width: 50px;
        height: 50px;
        animation: spin 2s linear infinite;
        margin: 0 auto;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>
