<template>
    <div class="animated fadeIn" v-show="loaded">
        <div class="card">
            <div class="card-header">
                <span style="float:left;">Bekijk {{album_name}}</span>

                <!--<button @click="toggleEditMode()" style="margin-left: 5px;" class="btn-core-header pull-right" v-bind:class="{ 'btn-success': edit_mode, 'btn-outline-success': !edit_mode }">Edit</button>-->
                <!--<album v-if="edit_mode" :overzicht=" 'AlbumsView' " :btn_class=" 'btn-core-header btn-primary pull-right' ":images="edit_images"></album>-->
            </div>
            <div class="card-body">
                <lightbox
                        id="mylightbox"
                        :images="images"
                        :image_class=" 'col-sm-6 col-md-3' "
                        :album_class=" 'row' "
                        :album_id="album_id">
                </lightbox>
            </div>
        </div>
    </div>
</template>

<script>
    import Lightbox from '../../components/Lightbox';
    import Album from '../../components/Album';

    export default {
        name: 'upload',
        data () {
            return {
                loaded: false,
                edit_mode: false,
                edit_images: [],
                album_id: null,
                album_name: '',
                images: []
            }
        },
        watch: {

        },
        mounted: function() {
            this.album_id = parseInt(this.$route.params.id);
            this.album_name = this.$route.params.name;

            let route = '/api/images';
            if(this.album_id === 0) {
                route = route+'/unlinked';
            }

            this.$http.post(
                route,
                JSON.stringify({from: 0, max: 40, album: this.album_id})
            )
                .then(
                    (response) => {
                        this.images = JSON.parse(response.body);
                        this.loaded = true;
                    }
                );
        },
        created: function() {

        },
        methods: {
            toggleEditMode: function() {
                this.edit_mode = !this.edit_mode;
            }
        },
        components: {
            Lightbox, Album
        }
    }
</script>

<style>
    .card {
        max-width: 1160px;
        margin: 0 auto;
    }
    .card-body img {
        width: 100%;
        transition: 0.2s ease-in-out;
    }
    .card-body img:hover {
        transform: scale(1.03) !important;
    }
    .album-name {
        font-weight: 700;
        text-align: center;
    }
    .album-items {
        text-align: center;
    }
</style>