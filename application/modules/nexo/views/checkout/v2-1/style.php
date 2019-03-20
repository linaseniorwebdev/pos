<style>
html, body {
  height: 100%;
  width: 100%;
}
body > div.wrapper {
  display: flex;
  flex-direction: column;
}
.content {
  display: flex;
  flex-direction: column;
  width: 100%;
}
.row.gui-row-tag {
  display: flex;
  flex-direction: row;
  flex: 1;
  margin-top: 15px;
}
.new-wrapper {
  flex: 1;
  min-height: 100%;
  height: 100%;
  display: flex;
}
.meta-row {
  display: flex;
  flex-direction: column;
}
.mb-0 {
  margin-bottom: 0px !important;
}
#cart-details-wrapper, #product-list-wrapper {
  display: flex;
  flex-direction: column;
  flex: 1;
}
#cart-details-wrapper .box-body, #product-list-wrapper .box-body {
  flex: 1;
  display: flex;
  flex-direction: column;
}
#cart-details-wrapper #cart-table-body {
  padding: 0px;
  display: flex;
  flex: 1;
}
#product-list-wrapper .item-list-container {
  flex: 1;
}

.calculator {
  padding: 10px;
  background-color: #ccc;
  border-radius: 5px;
  /*this is to remove space between divs that are inline-block*/
  font-size: 0;
}

.calculator > input[type=text] {
  width: 100%;
  height: 50px;
  border: none;
  background-color: #eee;
  text-align: right;
  font-size: 30px;
  padding-right: 10px;
}

.calculator .row { margin-top: 10px; }

.calculator .key {
    width: 63px;
    display: inline-block;
    background-color: white;
    color: #3e3e3e;
    font-size: 2rem;
    margin-right: 5px;
    border-radius: 5px;
    height: 50px;
    line-height: 50px;
    text-align: center;
    font-weight: 600;
    box-shadow: 0px 1px 2px 0px #333;
}

.calculator .key:active {
    box-shadow:inset 0px 2px 2px 0px #333;
}

.calculator .key:hover { cursor: pointer; }

.key.last { margin-right: 0px; }

.key.action { background-color: #f9f7c5; }
</style>