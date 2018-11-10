<template>
    <div v-if="this.show_modal" class="modal-mask" @click="$parent.$emit('closeModal')">
        <div class="modal-wrapper">
            <div class="modal-container">
                <img v-show="isLoad" :src="this.image_url" @load="loaded">
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "ImageViewModal",
        data () {
            return {
                isLoad: false
            }
        },
        props: {
            image_url: {
                type: String,
                required: true
            },
            show_modal: {
                type: Boolean,
                required: true
            }
        },
        methods: {
            loaded() {
                this.isLoad = true
            }
        }
    }
</script>

<style scoped>
    .modal-mask {
        position: fixed;
        z-index: 1000000000;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, .5);
        display: block;
        transition: opacity .3s ease;
    }

    .modal-wrapper {
        display: inline-block;
        position: relative;
        width: 100%;
        height: 100%;
        text-align: center;
    }

    .modal-container {
        display: inline;
        margin: 0;
        height: 100%;
    }

    .modal-container img {
        display: inline-block;
        position: relative;
        top: 50%;
        transform: translateY(-50%);
        max-height: 100%;
        max-width: 100%;
        vertical-align: middle;
        -webkit-box-shadow: 0 0 8px rgba(0, 0, 0, 0.6);
        -moz-box-shadow: 0 0 8px rgba(0, 0, 0, 0.6);
        box-shadow: 0 0 8px rgba(0, 0, 0, 0.6);
    }

    .modal-header h3 {
        margin-top: 0;
        color: #42b983;
    }
</style>