<template>
    <div class="animated fadeIn" v-show="loaded">
        <div class="card">
            <div class="card-header">Albums </div>
            <div class="card-body">
                <div class="col-sm-6 col-md-3 float-left" v-if="album_unlinked_count > 0">
                    <div @click="openAlbumNull()">
                        <img :src="'./static/img/folder.png'" alt="" />
                        <div class="album-name">Geen album</div>
                        <div class="album-items">items: {{album_unlinked_count}}</div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3 float-left" v-for="album in albums">
                    <div @click="openAlbum(album)">
                        <img :src="'./static/img/folder.png'" alt="" />
                        <div class="album-name">{{album.name}}</div>
                        <div class="album-items">items: {{album.amount}}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import Config from '../../../api-config'

    export default {
        name: 'upload',
        data () {
            return {
                albums: [],
                album_unlinked_count: 0,
                loaded: false
            }
        },
        watch: {

        },
        created: function() {
            this.fetchCategories();
            this.fetchNullCategories();
        },
        methods: {
            openAlbum: function(album) {
                this.$router.push({ name: 'AlbumsView', params: { id : album.id, name: album.name }});
            },
            openAlbumNull: function() {
                this.$router.push({ name: 'AlbumsView', params: { id : 0 }});
            },
            fetchCategories: function () {
                this.$http.get(
                    '/api/albums'
                ).then(function (response) {
                        this.albums = JSON.parse(response.body);
                        this.loaded = true;
                    }, (response) => {
                        console.log(response);
                    }
                );
            },
            fetchNullCategories: function () {
                this.$http.get(
                    '/api/images/unlinked/count'
                ).then(function (response) {
                        if(response.body[0][1] > 0) {
                            this.album_unlinked_count = response.body[0][1];
                        }

                        this.loaded = true;
                    }, (response) => {
                        console.log(response);
                    }
                );
            }
        },
        components: {
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