<template>
  <div v-if="polls.length">
    <div v-for="poll in polls" :key="poll.id">
      <h2>{{ poll.title }}</h2>
      <ul>
        <li v-for="(votes, answer) in poll.answers" :key="answer">
          {{ answer }}: {{ votes }}
          <button @click="vote(poll.id, answer)">Vote</button>
        </li>
      </ul>
    </div>
  </div>
  <div v-else>
    Loading polls...
  </div>
</template>

<script>
export default {
  data() {
    return {
      polls: [],
    };
  },
  mounted() {
    this.fetchPolls();
  },
  methods: {
    async fetchPolls() {
      const response = await fetch('http://webdevtest.local/wp-json/my-poll/v1/polls');
      const data = await response.json();
      this.polls = data;
    },
    async vote(pollId, answer) {
      await fetch(`http://webdevtest.local/wp-json/my-poll/v1/poll/${pollId}`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ answer }),
      });
      this.fetchPolls(); // Refresh the polls to get the updated vote count
    },
  },
};
</script>
