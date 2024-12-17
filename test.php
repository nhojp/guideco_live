<?php
include 'head.php';
include 'student-nav.php';
?>
<main class="flex-fill mt-5">
    <div class="container mt-4">
        <h1 class="text-center font-weight-bold">HOW DO YOU FEEL TODAY?</h1>

        <div class="container-red">
            <form id="feelingForm" class="text-center">
                <div>
                    <h3>Physically</h3>
                    <div class="feeling-option">
                        <input type="radio" id="sick" name="physical_feeling" value="sick" required>
                        <label for="sick">
                            <img src="img/sick.png" alt="Sick">
                            <span>Sick</span>
                        </label>
                    </div>
                    <div class="feeling-option">
                        <input type="radio" id="tired" name="physical_feeling" value="tired" required>
                        <label for="tired">
                            <img src="img/tired.png" alt="Tired">
                            <span>Tired</span>
                        </label>
                    </div>
                    <div class="feeling-option">
                        <input type="radio" id="energetic" name="physical_feeling" value="energetic" required>
                        <label for="energetic">
                            <img src="img/laughing.png" alt="Energetic">
                            <span>Energetic</span>
                        </label>
                    </div>
                </div>

                <div>
                    <h3 class="mt-4">Emotionally</h3>
                    <div class="feeling-option">
                        <input type="radio" id="happy" name="emotional_feeling" value="happy" required>
                        <label for="happy">
                            <img src="img/happy.png" alt="Happy">
                            <span>Happy</span>
                        </label>
                    </div>
                    <div class="feeling-option">
                        <input type="radio" id="sad" name="emotional_feeling" value="sad" required>
                        <label for="sad">
                            <img src="img/sad.png" alt="Sad">
                            <span>Sad</span>
                        </label>
                    </div>
                    <div class="feeling-option">
                        <input type="radio" id="angry" name="emotional_feeling" value="angry" required>
                        <label for="angry">
                            <img src="img/angry.png" alt="Angry">
                            <span>Angry</span>
                        </label>
                    </div>
                </div>

                <div>
                    <h3 class="mt-4">Mentally</h3>
                    <div class="feeling-option">
                        <input type="radio" id="calm" name="mental_feeling" value="calm" required>
                        <label for="calm">
                            <img src="img/calm.png" alt="Calm">
                            <span>Calm</span>
                        </label>
                    </div>
                    <div class="feeling-option">
                        <input type="radio" id="stressed" name="mental_feeling" value="stressed" required>
                        <label for="stressed">
                            <img src="img/hypnotized.png" alt="Stressed">
                            <span>Stressed</span>
                        </label>
                    </div>
                    <div class="feeling-option">
                        <input type="radio" id="anxious" name="mental_feeling" value="anxious" required>
                        <label for="anxious">
                            <img src="img/anxious.png" alt="Anxious">
                            <span>Anxious</span>
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn btn-success mt-3">Get Recommendation</button>
            </form>
        </div>

        <!-- Modal for Result -->
        <div class="modal fade" id="resultModal" tabindex="-1" aria-labelledby="resultModalLabel" aria-hidden="true">
            <div class="modal-dialog custom-modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="resultModalLabel">Guideco Recommends you to</h5>
                    </div>
                    <div class="modal-body font-weight-bold justify-content-center align-items-center" id="resultContent">
                        <!-- Result will be shown here -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.7/dist/umd/popper.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>

        <script>
            document.getElementById('feelingForm').addEventListener('submit', function(event) {
                event.preventDefault();

                // Get values from form
                const physicalFeeling = document.querySelector('input[name="physical_feeling"]:checked').value;
                const emotionalFeeling = document.querySelector('input[name="emotional_feeling"]:checked').value;
                const mentalFeeling = document.querySelector('input[name="mental_feeling"]:checked').value;

                // Send the data to the backend (predict endpoint)
                fetch('https://guideco.pythonanywhere.com/predict', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        physical_feeling: physicalFeeling,
                        emotional_feeling: emotionalFeeling,
                        mental_feeling: mentalFeeling
                    })
                })
                .then(response => response.json())
                .then(data => {
                    // Handle successful response
                    if (data && data.recommendation) {
                        document.getElementById('resultContent').innerText = data.recommendation;
                    } else {
                        document.getElementById('resultContent').innerText = 'No recommendation available at the moment.';
                    }
                    var resultModal = new bootstrap.Modal(document.getElementById('resultModal'));
                    resultModal.show();
                })
                .catch(error => {
                    // Handle error
                    console.error('Error:', error);
                    document.getElementById('resultContent').innerText = 'An error occurred. Please try again.';
                    var resultModal = new bootstrap.Modal(document.getElementById('resultModal'));
                    resultModal.show();
                });
            });
        </script>
    </div>
</main>