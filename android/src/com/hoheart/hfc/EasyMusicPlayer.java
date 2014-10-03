package com.hoheart.hfc;

import java.io.IOException;

import android.content.Context;
import android.media.MediaPlayer;
import android.os.Handler;
import android.os.Message;
import android.view.View;
import android.widget.ImageButton;
import android.widget.SeekBar;
import android.widget.SeekBar.OnSeekBarChangeListener;

/**
 * 包含了一个进度条
 * 
 * @author Hoheart
 * 
 */
public class EasyMusicPlayer {

	public enum State {
		NOT_INITED, INITED, READY, PLAYING, PAUSE
	}

	public interface OnStateChangeListener {

		public void onStateChange(State s);
	}

	private OnSeekBarChangeListener mSeekBarChangeListener = new OnSeekBarChangeListener() {

		@Override
		public void onProgressChanged(SeekBar seekBar, int progress,
				boolean fromUser) {
		}

		@Override
		public void onStartTrackingTouch(SeekBar seekBar) {
		}

		@Override
		public void onStopTrackingTouch(SeekBar seekBar) {
			int dest = seekBar.getProgress();

			int mMax = mPlayer.getDuration();
			int sMax = seekBar.getMax();

			mPlayer.seekTo(mMax * dest / sMax);
		}

	};

	private Handler mHandle = new Handler() {
		@Override
		public void handleMessage(Message msg) {
			SeekBar seekBar = (SeekBar) mRootView
					.findViewById(R.id.PlayerSeekBar);
			if (null == seekBar) {
				return;
			}

			int position = mPlayer.getCurrentPosition();

			int mMax = mPlayer.getDuration();
			int sMax = seekBar.getMax();

			int p = 0;
			if (mMax > 0) {
				p = position * sMax / mMax;
			}
			seekBar.setProgress(p);
		}
	};

	private Thread mSeekThread = new Thread() {

		private int milliseconds = 500;

		public void run() {
			while (true) {
				try {
					sleep(milliseconds);
				} catch (InterruptedException e) {
					e.printStackTrace();
				}

				mHandle.sendEmptyMessage(0);
			}
		}
	};

	private MediaPlayer mPlayer = null;
	private View mRootView = null;
	private OnStateChangeListener mOnStateChangeListener = null;
	private State mState = State.NOT_INITED;

	public EasyMusicPlayer(Context context) {
		mPlayer = new MediaPlayer();
		mSeekThread.start();

		mRootView = View.inflate(context, R.layout.easy_music_player, null);

		attachEvent();

		changeState(State.INITED);
	}

	public void setOnStateChangeListener(OnStateChangeListener l) {
		mOnStateChangeListener = l;
	}

	public void setDataSource(String path) throws IllegalArgumentException,
			IllegalStateException, IOException {
		mPlayer.stop();
		mPlayer.reset();
		mPlayer.setDataSource(path);

		mPlayer.prepare();

		changeState(State.READY);
	}

	public void pause() {
		mPlayer.pause();

		changeState(State.PAUSE);
	}

	public void start() {
		mPlayer.start();

		changeState(State.PLAYING);
	}

	public void stop() {
		if (mPlayer.isPlaying()) {
			mPlayer.pause();
			mPlayer.seekTo(0);
		}

		changeState(State.READY);
	}

	public void playOrPause() {
		if (mPlayer.isPlaying()) {
			pause();
		} else {
			start();
		}
	}

	public View getView() {
		return mRootView;
	}

	public State getState() {
		return mState;
	}

	private void attachEvent() {
		SeekBar seekBar = (SeekBar) mRootView.findViewById(R.id.PlayerSeekBar);
		seekBar.setOnSeekBarChangeListener(mSeekBarChangeListener);

		View btnClose = mRootView.findViewById(R.id.btn_player_close);
		btnClose.setOnClickListener(new View.OnClickListener() {

			@Override
			public void onClick(View v) {
				mRootView.setVisibility(View.GONE);
			}
		});

		View btnPlay = mRootView.findViewById(R.id.btn_player_start);
		btnPlay.setOnClickListener(new View.OnClickListener() {

			@Override
			public void onClick(View v) {
				playOrPause();
			}
		});

		View btnStop = mRootView.findViewById(R.id.btn_player_stop);
		btnStop.setOnClickListener(new View.OnClickListener() {

			@Override
			public void onClick(View v) {
				stop();
			}
		});

		mPlayer.setOnCompletionListener(new MediaPlayer.OnCompletionListener() {

			@Override
			public void onCompletion(MediaPlayer mp) {
				stop();
			}

		});
	}

	private void changeState(State s) {
		mState = s;

		ImageButton btnPlayerStart = (ImageButton) mRootView
				.findViewById(R.id.btn_player_start);
		if (State.PLAYING == s) {
			btnPlayerStart
					.setImageResource(R.drawable.image_btn_pause_selector);
		} else {
			btnPlayerStart.setImageResource(R.drawable.image_btn_play_selector);
		}

		if (null != mOnStateChangeListener) {
			mOnStateChangeListener.onStateChange(s);
		}
	}
}
