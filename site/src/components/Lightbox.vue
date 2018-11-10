<template>
    <div>
        <div class="my-gallery" :class="album_class">
            <div :class="image_class" v-for="image,key in images">
                <img height="180"
                     :src="image.filename|thumbUrl"
                     @click="viewImage(image.filename)"
                     />
            </div>
            <ImageViewModal
                    :show_modal="this.show_modal"
                    :image_url="this.image_url">
            </ImageViewModal>
            <div v-bind:class="{ 'loader': loading }"></div>
        </div>
    </div>
</template>

<script>
    import Config from '../../api-config'
    import ImageViewModal from "./ImageViewModal";

    export default {
        components: {ImageViewModal},
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
        created() {
            this.$on('closeModal', section => {
                console.log(2);
                this.show_modal = false;
            });
        },
        data() {
            return {
                loading: false,
                done: false,
                from: 40,
                max: 20,
                image_url: '',
                show_modal: false
            }
        },
        mounted() {
            window.onscroll = () => {
                var pageHeight=document.documentElement.offsetHeight,
                    windowHeight=window.innerHeight,
                    scrollPosition=window.scrollY || window.pageYOffset || document.body.scrollTop + (document.documentElement && document.documentElement.scrollTop || 0);

                console.log(pageHeight, windowHeight+scrollPosition);
                if ((pageHeight-1) <= windowHeight+scrollPosition) {
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

                        }, (response) => {
                            console.log(response);
                        }
                    );
                }

            },
            viewImage: function (url) {
                this.show_modal = true;
                this.image_url = Config.base_api_url+'/images/resize/'+url;
            },
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

    .my-gallery > div > img {
        width: 100%;
        margin-bottom: 30px;
        transition: 0.2s ease-in-out;
        box-shadow: 0 2px 3px rgba(0,0,0,0.2);
        border-radius: 4px;
    }

    .my-gallery > div > img:hover {
        transform: scale(1.03) !important;
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
