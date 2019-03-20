
<style>
.button-wrapper .btn {
    box-shadow: 1px 1px 1px 1px #cecece;
    border: solid 1px #c5c5c5;
}
.img-grid {
    width: 100%;
    height: 100%;
    min-height: 100%;
    min-width: 100%;
    background: #EEE;
    overflow: hidden;
}
.cart-container {
    background: #FFF;
}
.product-item-details {
    position: absolute;
    bottom: 5px;
    min-height: 70px;
    background: #17171757;
    color: #FFF;
    width: 95%;
    padding: 3px;
    margin: auto;
    font-size: 14px;
}
html, body {
    height:100%;
}
.product-grid-item {
    background: #FEFEFE;
    max-height:150px;
    border: 1px solid #eeeeee3b;
}
.product-grid-item.active:hover {
    box-shadow:inset 0px 0px 0px 4px #7a84fb;
}
.product-grid-item.active {
    box-shadow:inset 0px 0px 0px 4px #7a84fb;
}
.product-grid-item:hover {
    cursor: pointer;
    box-shadow: inset 0px 0px 2px #EEE;
}
@media screen and (max-width: 1200px) and (min-width: 901px) {
    .product-grid-item {
        height:150px;
    }
}
@media screen and (max-width: 900px) and (min-width: 766px) {
    .product-grid-item {
        height:100px;
    }
}
@media screen and (max-width: 765px) {
    .product-grid-item {
        height:200px;
    }
}
#modal-vue-modifier-modal .modal-body, 
#modal-vue-login-modal .modal-body,
#modal-vue-ask-person .modal-body, 
#modal-vue-registration-popup .modal-body {
    display: flex;
    flex-direction: column;
    flex-basis: 0;
    overflow-y: scroll;
}
</style>
<style>
.lds-roller {
  display: inline-block;
  position: relative;
  width: 64px;
  height: 64px;
}
.lds-roller div {
  animation: lds-roller 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite;
  transform-origin: 32px 32px;
}
.lds-roller div:after {
  content: " ";
  display: block;
  position: absolute;
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: #333;
  margin: -3px 0 0 -3px;
}
.lds-roller div:nth-child(1) {
  animation-delay: -0.036s;
}
.lds-roller div:nth-child(1):after {
  top: 50px;
  left: 50px;
}
.lds-roller div:nth-child(2) {
  animation-delay: -0.072s;
}
.lds-roller div:nth-child(2):after {
  top: 54px;
  left: 45px;
}
.lds-roller div:nth-child(3) {
  animation-delay: -0.108s;
}
.lds-roller div:nth-child(3):after {
  top: 57px;
  left: 39px;
}
.lds-roller div:nth-child(4) {
  animation-delay: -0.144s;
}
.lds-roller div:nth-child(4):after {
  top: 58px;
  left: 32px;
}
.lds-roller div:nth-child(5) {
  animation-delay: -0.18s;
}
.lds-roller div:nth-child(5):after {
  top: 57px;
  left: 25px;
}
.lds-roller div:nth-child(6) {
  animation-delay: -0.216s;
}
.lds-roller div:nth-child(6):after {
  top: 54px;
  left: 19px;
}
.lds-roller div:nth-child(7) {
  animation-delay: -0.252s;
}
.lds-roller div:nth-child(7):after {
  top: 50px;
  left: 14px;
}
.lds-roller div:nth-child(8) {
  animation-delay: -0.288s;
}
.lds-roller div:nth-child(8):after {
  top: 45px;
  left: 10px;
}
@keyframes lds-roller {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}
.overlay-loader {
    display: flex;
    position: fixed;
    width: 100%;
    height: 100%;
    z-index: 9999;
    top: 0;
    background: rgba(51, 51, 51, 0.2);
    flex-direction: row;
    align-items: center;
}
</style>
