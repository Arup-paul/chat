<template>
  <div>
    <div class="container-chat clearfix">
      <div class="row">
        <div class="people-list" id="people-list">
          <div class="search">
            <input type="text" placeholder="search" />
            <i class="fa fa-search"></i>
          </div>
          <ul class="list">
            <li
              @click.prevent="selectUser(user.id)"
              class="clearfix"
              v-for="user in userList"
              :key="user.id"
            >
              <img
                src="https://s3-us-west-2.amazonaws.com/s.cdpn.io/195612/chat_avatar_01.jpg"
                alt="avatar"
              />
              <div class="about">
                <div class="name">{{user.name}}</div>

                <div class="status">
                  <div v-if="onlineuser(user.id) || online.id ==user.id">
                    <i class="fa fa-circle online">online</i>
                  </div>
                  <div v-else>
                    <i class="fa fa-circle">offline</i>
                  </div>
                </div>
              </div>
            </li>
          </ul>
        </div>
        <div class="chat">
          <div class="chat-header clearfix">
            <img
              src="https://s3-us-west-2.amazonaws.com/s.cdpn.io/195612/chat_avatar_01_green.jpg"
              alt="avatar"
            />

            <div class="chat-about">
              <div v-if="userMessage.user" class="chat-with">{{userMessage.user.name}}</div>
              <div class="chat-num-messages">already 1 902 messages</div>
            </div>
            <i class="fa fa-star"></i>
            <ul class="nav nav-tabs">
              <li class="nav-item dropdown">
                <a
                  class="nav-link dropdown-toggle"
                  data-toggle="dropdown"
                  href="#"
                  role="button"
                  aria-haspopup="true"
                  aria-expanded="false"
                >...</a>
                <div class="dropdown-menu">
                  <a @click.prevent="deleteAllMessage" class="dropdown-item">Delete All Message</a>
                </div>
              </li>
            </ul>
          </div>
          <!-- end chat-header -->

          <div class="chat-history" v-chat-scroll>
            <ul>
              <li class="clearfix" v-for="message in userMessage.messages" :key="message.id">
                <div class="message-data align-right">
                  <span class="message-data-time">{{message.created_at | timeformat}}</span> &nbsp; &nbsp;
                  <span class="message-data-name">{{message.user.name}}</span>
                  <i class="fa fa-circle me">
                    <ul class="nav nav-tabs">
                      <li class="nav-item dropdown">
                        <a
                          class="nav-link dropdown-toggle"
                          data-toggle="dropdown"
                          href="#"
                          role="button"
                          aria-haspopup="true"
                          aria-expanded="false"
                        >...</a>
                        <div class="dropdown-menu">
                          <a
                            @click.prevent="deleteSignleMessage(message.id)"
                            class="dropdown-item"
                          >Delete Message</a>
                        </div>
                      </li>
                    </ul>
                  </i>
                </div>
                <div
                  :class="`message float-right ${message.user.id==userMessage.user.id ? 'my-message' : 'other-message'}`"
                >{{message.message}}</div>
              </li>
            </ul>
          </div>
          <!-- end chat-history -->

          <div class="chat-message clearfix">
            <p v-if="typing">{{typing}} typing...</p>
            <textarea
              v-if="userMessage.user"
              @keydown="typingEvent(userMessage.user.id)"
              @keydown.enter="sendMessage"
              v-model="message"
              name="message-to-send"
              id="message-to-send"
              placeholder="Type your message"
              rows="3"
            ></textarea>
            <textarea
              @keydown.enter="sendMessage"
              v-else
              disabled
              name="message-to-send"
              id="message-to-send"
              placeholder="Type your message"
              rows="3"
            ></textarea>

            <i class="fa fa-file-o"></i> &nbsp;&nbsp;&nbsp;
            <i class="fa fa-file-image-o"></i>

            <button type="submit">Send</button>
          </div>
          <!-- end chat-message -->
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import _ from "lodash";
export default {
  mounted() {
    Echo.private(`chat.${authuser.id}`).listen("MessageSend", (e) => {
      this.selectUser(e.message.from);
    });
    this.$store.dispatch("userList");

    Echo.private("typingevent").listenForWhisper("typing", (e) => {
      if (e.user.id == this.userMessage.user.id && authuser.id == e.userId) {
        this.typing = e.user.name;
      }

      setTimeout(() => {
        this.typing = "";
      }, 2000);
    });
  },
  data() {
    return {
      message: "",
      typing: "",
      users: [],
      online:''
    };
  },
  computed: {
    userList() {
      return this.$store.getters.userList;
    },
    userMessage() {
      return this.$store.getters.userMessage;
    },
  },

  created() {
    Echo.join("liveuser")
      .here((users) => {
        this.users = users;
      })
      .joining((user) => {
        this.online = user;
      })
      .leaving((user) => {
        console.log(user.name);
      });
  },
  methods: {
    selectUser(userId) {
      this.$store.dispatch("userMessage", userId);
    },
    sendMessage(e) {
      e.preventDefault();
      if (this.message != "") {
        axios
          .post("/sendmessage", {
            message: this.message,
            user_id: this.userMessage.user.id,
          })
          .then((response) => {
            this.selectUser(this.userMessage.user.id);
          });
        this.message = "";
      }
    },
    deleteSignleMessage(messageId) {
      axios.get(`/deleteSingleMessage/${messageId}`).then((response) => {
        this.selectUser(this.userMessage.user.id);
      });
    },
    deleteAllMessage(messageId) {
      axios
        .get(`/deleteAllMessage/${this.userMessage.user.id}`)
        .then((response) => {
          this.selectUser(this.userMessage.user.id);
        });
    },
    typingEvent(userId) {
      Echo.private("typingevent").whisper("typing", {
        user: authuser,
        typing: this.message,
        userId: userId,
      });
    },
    onlineuser(userId) {
      return _.find(this.users, { id: userId });
    },
  },
};
</script>

<style>
.nav-tabs .nav-link {
  font-size: 15px;
}
</style>
