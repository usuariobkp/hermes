package gcba.ratings.example;

import android.support.v7.app.AppCompatActivity;
import android.os.Bundle;

import gcba.ratings.sdk.Rating;

public class MainActivity extends AppCompatActivity {

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);

        setContentView(R.layout.activity_main);

        Thread t = new Thread(new Runnable() {
            public void run() {
                String app = "e10adc3949ba59abbe56e057f20f883e";
                String platform = app;
                String range = app;
                String token = app;

                try{
                    Rating rating = new Rating("https://527ff015.ngrok.io", app, platform, range, token);

                    // Rating only; no user

                    rating.create(5);

                    // Rating and description only; no user

                    rating.create(4, "Bueno");

                    // Rating, description and comment; no user

                    rating.create(3, "Regular", "Lorem ipsum dolor...");

                    // Rating, description and comment; user name and mibaId only

                    rating.setUser("Juan Pérez", "e10adc394");
                    rating.create(2, "Regular", "Lorem ipsum dolor...");

                    // Rating, description and comment; user name and email only

                    rating.setUser("Juan Pérez", null, "juan@example.com");
                    rating.create(1, "Muy Malo", "Lorem ipsum dolor...");

                    // Rating, description and comment; user name, email and mibaId

                    rating.setUser("Juan Pérez", "e10adc394", "juan@perez.com");
                    rating.create(5, "Muy Bueno", "Lorem ipsum dolor...");
                } catch(Error e) {
                    System.err.println ("Error: " + e.getMessage());
                }
            }
        });

        t.start();
    }
}
